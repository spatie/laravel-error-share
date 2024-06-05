@php
    $properties = app(\Spatie\LaravelErrorShare\Actions\ResolveErrorSharePropertiesAction::class)->execute($exception);
@endphp
@unless(array_key_exists('error', $properties) || config('error-share.enabled') === false)
    <div
        x-data="{
            sharePanelOpen: false,
            shareStack: true,
            shareContext: true,
            shareDebug: true,
            shareError: null,

            toggle() {
                if (this.sharePanelOpen) {
                    return this.close();
                }

                this.$refs.button.focus();

                this.sharePanelOpen = true;
            },

            close(focusAfter) {
                if (!this.sharePanelOpen) {
                    return;
                }

                this.sharePanelOpen = false;

                if (focusAfter) {
                    focusAfter.focus();
                }
            },

            sendShare() {
                if (!this.shareStack && !this.shareContext && !this.shareDebug) {
                    this.shareError = 'You must select at least one tab to share.';

                    return;
                }

                let report = {{ Js::from($properties['report']) }};

                let tabs = [];

                if (this.shareStack) {
                    tabs.push('stackTraceTab');
                } else {
                    report.stacktrace = report.stacktrace.slice(0, 1);
                }

                if (this.shareContext) {
                    tabs.push('contextTab', 'requestTab', 'appTab', 'userTab');
                } else {
                    report.context.request_data = {
                        queryString: {},
                        body: {},
                        files: [],
                    };
                    report.context.headers = {};
                    report.context.cookies = {};
                    report.context.route = null;
                }

                if (this.shareDebug) {
                    tabs.push('debugTab');
                } else {
                    report.context.queries = [];
                }

                fetch('{{ $properties['url'] }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        report,
                        tabs,
                        lineSelection: window.location.hash,
                    }),
                })
                    .then((response) => {
                        if (response.ok) {
                            return response.json();
                        } else {
                            throw new Error('Something went wrong.');
                        }
                    })
                    .then((data) => {
                        let anchorElement = document.createElement('a');
                        anchorElement.href = data.owner_url;
                        anchorElement.target = '_blank';
                        document.body.appendChild(anchorElement);
                        anchorElement.click();
                        document.body.removeChild(anchorElement);
                    })
                    .catch((error) => {
                        this.shareError = String(error).replace('Error: ', '');
                    });
            },
        }"
        x-on:keydown.escape.prevent.stop="close($refs.button)"
        x-on:focusin.window="! $refs.panel.contains($event.target) && close()"
        x-id="['dropdown-button']"
        class="relative"
    >
        <!-- Button -->
        <button
            x-ref="button"
            x-on:click="toggle()"
            :aria-expanded="sharePanelOpen"
            :aria-controls="$id('dropdown-button')"
            type="button"
            class="flex items-center gap-2 rounded-md hover:bg-gray-100/75 hover:bg-gray-100 p-1 text-sm leading-5 text-gray-900 dark:hover:bg-gray-800/75 dark:text-white"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 15 6-6m0 0-6-6m6 6H9a6 6 0 0 0 0 12h3" />
            </svg>
            <span style="transform: translateY(1px)">Share</span>
        </button>

        <!-- Panel -->
        <div
            x-ref="panel"
            x-show="sharePanelOpen"
            x-on:click.outside="close($refs.button)"
            :id="$id('dropdown-button')"
            style="display: none; width: 15rem"
            class="absolute right-0 z-10 flex origin-top-right flex-col rounded-md bg-white dark:text-white shadow-xl ring-1 ring-gray-900/5 dark:bg-gray-800"
        >
            <div class="flex flex-col gap-2 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Share with Flare</h2>
                    <a
                        class="text-gray-500 dark:text-white"
                        href="https://flareapp.io/docs/ignition/introducing-ignition/sharing-errors?utm_campaign=ignition&amp;utm_source=ignition"
                        target="_blank"
                        rel="noreferrer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                        </svg>
                    </a>
                </div>
                <ul style="list-style: none">
                    <li class="flex items-center gap-2">
                        <input type="checkbox" name="share_stack" id="share_stack" x-model="shareStack">
                        <label for="share_stack">Stack trace</label>
                    </li>
                    <li class="flex items-center gap-2">
                        <input type="checkbox" name="share_context" id="share_context" x-model="shareContext">
                        <label for="share_context">Request context</label>
                    </li>
                    <li class="flex items-center gap-2">
                        <input type="checkbox" name="share_debug" id="share_debug" x-model="shareDebug">
                        <label for="share_debug">Database queries</label>
                    </li>
                </ul>
                <div>
                    <button
                        style="background-color: rgb(139, 92, 246); color: white"
                        class="text-sm rounded-full px-3 py-2 flex gap-2 font-bold leading-5"
                        type="submit"
                        x-on:click="sendShare()"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" style="transform: translateY(-1px)">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                        Create share
                    </button>
                </div>
                <template x-if="shareError">
                    <p class="p-2 text-xs border-l-red-500 text-red-500 bg-red-500/20 rounded-md bg-color" x-text="shareError"></p>
                </template>
            </div>
        </div>
    </div>
@endunless
