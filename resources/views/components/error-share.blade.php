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
            shared: false,
            sharedUrl: null,
            shareError: null,
            toggle() {
                if (this.sharePanelOpen) {
                    return this.close()
                }

                this.$refs.button.focus()

                this.sharePanelOpen = true
            },
            close(focusAfter) {
                if (! this.sharePanelOpen) return

                this.sharePanelOpen = false

                focusAfter && focusAfter.focus()
            },
            sendShare() {
                if(! this.shareStack && ! this.shareContext && ! this.shareDebug){
                    this.shareError = 'you must select at least one tab to share';

                    return;
                }

                let report = {{ Js::from($properties['report']) }};

                let tabs = [];

                if (this.shareStack) {
                    tabs.push('stackTraceTab');
                }else{
                    report.stacktrace = report.stacktrace.slice(0, 1);
                }

                if (this.shareContext) {
                    tabs.push('contextTab', 'requestTab', 'appTab', 'userTab');
                }else{
                    report.context.request_data = { queryString: {}, body: {}, files: [] };
                    report.context.headers = {};
                    report.context.cookies = {};
                    report.context.route = null;
                }

                if (this.shareDebug) {
                    tabs.push('debugTab');
                }else{
                    report.context.queries = [];
                }

                fetch('{{ $properties['url'] }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        report,
                        tabs,
                        lineSelection: window.location.hash,
                    }),
                })
                    .then(response => {
                        if (response.ok) {
                            return response.json(); // parse the response body as JSON
                        } else {
                            throw new Error('could not share the error to Flare');
                        }
                    })
                    .then(data => {
                        this.sharedUrl = data.owner_url;
                        this.shared = true;
                    })
                    .catch(error => {
                        this.shareError = error;
                    });
            },
            copySharedUrlToClipboard() {
                const el = document.createElement('textarea');
                el.value = this.sharedUrl;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
            }
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
            class="flex items-center gap-2 rounded-full hover:bg-gray-100/75 bg-gray-100 px-3 py-2 text-sm leading-5 text-gray-900 dark:bg-gray-800 dark:hover:bg-gray-800/75 dark:text-white"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 512 512" fill="currentColor">
                <path d="M307 34.8c-11.5 5.1-19 16.6-19 29.2v64H176C78.8 128 0 206.8 0 304C0 417.3 81.5 467.9 100.2 478.1c2.5 1.4 5.3 1.9 8.1 1.9c10.9 0 19.7-8.9 19.7-19.7c0-7.5-4.3-14.4-9.8-19.5C108.8 431.9 96 414.4 96 384c0-53 43-96 96-96h96v64c0 12.6 7.4 24.1 19 29.2s25 3 34.4-5.4l160-144c6.7-6.1 10.6-14.7 10.6-23.8s-3.8-17.7-10.6-23.8l-160-144c-9.4-8.5-22.9-10.6-34.4-5.4z"/>
            </svg>
            Share
        </button>

        <!-- Panel -->
        <div
            x-ref="panel"
            x-show="sharePanelOpen"
            x-transition.origin.top.left
            x-on:click.outside="close($refs.button)"
            :id="$id('dropdown-button')"
            style="display: none;width: 15rem"
            class="absolute left-0 mt-2 rounded-md bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 p-4 shadow-xl ring-1 ring-gray-900/5"
        >
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Share with Flare</h2>
                    <a
                        class="text-xs flex items-center underline transition-colors"
                        style="text-decoration: underline"
                        href="https://flareapp.io/docs/ignition/introducing-ignition/sharing-errors?utm_campaign=ignition&amp;utm_source=ignition"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Docs
                        <svg height="58" viewBox="0 0 38 58" width="38" xmlns="http://www.w3.org/2000/svg" class="w-4 h-5 ml-1 ">
                            <linearGradient id="a" x1="50%" x2="50%" y1="100%" y2="0%">
                                <stop offset="0" stop-color="#48b987"></stop>
                                <stop offset="1" stop-color="#137449"></stop>
                            </linearGradient>
                            <linearGradient id="b" x1="50%" x2="50%" y1="0%" y2="100%">
                                <stop offset="0" stop-color="#66ffbc"></stop>
                                <stop offset="1" stop-color="#218e5e"></stop>
                            </linearGradient>
                            <linearGradient id="c" x1="81.686741%" x2="17.119683%" y1="50%" y2="46.893103%">
                                <stop offset="0" stop-color="#ccffe7" stop-opacity=".492379"></stop>
                                <stop offset=".37576486" stop-color="#fff" stop-opacity=".30736"></stop>
                                <stop offset="1" stop-color="#00ff85" stop-opacity="0"></stop>
                            </linearGradient>
                            <linearGradient id="d" x1="50%" x2="50%" y1="100%" y2="0%">
                                <stop offset="0" stop-color="#a189f2"></stop>
                                <stop offset="1" stop-color="#3f00f5"></stop>
                            </linearGradient>
                            <linearGradient id="e" x1="50%" x2="50%" y1="0%" y2="100%">
                                <stop offset="0" stop-color="#bbadfa"></stop>
                                <stop offset="1" stop-color="#9275f4"></stop>
                            </linearGradient>
                            <g fill="none">
                                <g transform="translate(1 1)">
                                    <path d="m11.9943899 27.9858314-11.9943899-6.9992916v-13.98724823l12.0617111 7.02196133z" fill="url(#a)"></path>
                                    <path d="m23.9775596 20.9808724-23.9775596-13.98158083 11.9943899-6.99929157 24.0056101 13.9815808z" fill="url(#b)" stroke="url(#c)"></path>
                                </g>
                                <g transform="translate(1 29.014169)">
                                    <path d="m11.9943899 27.9858314-11.9943899-6.9936241v-13.99291573l11.9663394 6.99362413z" fill="url(#d)"></path>
                                    <path d="m11.9663394 13.9929157-11.9663394-6.99362413 11.9943899-6.99929157 11.9943899 6.99929157z" fill="url(#e)"></path>
                                </g>
                            </g>
                        </svg>
                    </a>
                </div>
                <template x-if="! shared">
                    <div style="display: contents">
                        <ul style="list-style: none">
                            <li class="flex items-center gap-2">
                                <input type="checkbox" name="share_stack" id="share_stack" x-model="shareStack">
                                <label for="share_stack">Stack</label>
                            </li>
                            <li class="flex items-center gap-2">
                                <input type="checkbox" name="share_context" id="share_context" x-model="shareContext">
                                <label for="share_context">Context</label>
                            </li>
                            <li class="flex items-center gap-2">
                                <input type="checkbox" name="share_debug" id="share_debug" x-model="shareDebug">
                                <label for="share_debug">Debug</label>
                            </li>
                        </ul>
                        <div>
                            <button
                                style="background-color: rgba(139,92,246);border-color: rgba(139,92,246,.25);color: white;border-bottom-width:1px; height:2rem;letter-spacing: .05em;font-weight: 700;font-size: .75rem;line-height: 1rem;border-radius: .125rem;text-transform: uppercase;white-space: nowrap;"
                                class="flex items-center gap-2 px-4 h-8 whitespace-nowrap text-xs uppercase tracking-wider font-bold rounded-sm shadow-md transform transition-animation hover:shadow-lg active:shadow-inner active:translate-y-px opacity-100"
                                type="submit"
                                x-on:click="sendShare()"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 512 512" fill="currentColor">
                                    <path d="M352 0c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9L370.7 96 201.4 265.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L416 141.3l41.4 41.4c9.2 9.2 22.9 11.9 34.9 6.9s19.8-16.6 19.8-29.6V32c0-17.7-14.3-32-32-32H352zM80 32C35.8 32 0 67.8 0 112V432c0 44.2 35.8 80 80 80H400c44.2 0 80-35.8 80-80V320c0-17.7-14.3-32-32-32s-32 14.3-32 32V432c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16H192c17.7 0 32-14.3 32-32s-14.3-32-32-32H80z"/>
                                </svg>
                                Create share
                            </button>
                        </div>
                        <template x-if="shareError">
                            <div class="p-2 text-xs border-l-red-500 text-red-500 bg-red-500/20 rounded-md bg-color">
                                Something went wrong: <span x-text="shareError"></span>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="shared">
                    <div style="display: contents">
                        <p class="text-sm mb-1">Share your error with others</p>
                        <div class="flex gap-2 items-center">
                            <a
                                :href="sharedUrl"
                                style="background-color: rgba(139,92,246);border-color: rgba(139,92,246,.25);color: white;border-bottom-width:1px; height:2rem;letter-spacing: .05em;font-weight: 700;font-size: .75rem;line-height: 1rem;border-radius: .125rem;text-transform: uppercase;white-space: nowrap;"
                                class="flex items-center gap-2 px-4 h-8 whitespace-nowrap text-xs uppercase tracking-wider font-bold rounded-sm shadow-md transform transition-animation hover:shadow-lg active:shadow-inner active:translate-y-px opacity-100"
                                type="submit"
                                x-on:click="sendShare()"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 512 512" fill="currentColor">
                                    <path d="M352 0c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9L370.7 96 201.4 265.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L416 141.3l41.4 41.4c9.2 9.2 22.9 11.9 34.9 6.9s19.8-16.6 19.8-29.6V32c0-17.7-14.3-32-32-32H352zM80 32C35.8 32 0 67.8 0 112V432c0 44.2 35.8 80 80 80H400c44.2 0 80-35.8 80-80V320c0-17.7-14.3-32-32-32s-32 14.3-32 32V432c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16H192c17.7 0 32-14.3 32-32s-14.3-32-32-32H80z"/>
                                </svg>
                                Visit public share
                            </a>

                            <button
                                title="Copy to clipboard"
                                type="button"
                                class="bg-gray-700 h-6 w-6 rounded-md text-sm"
                                x-on:click="copySharedUrlToClipboard()"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 448 512" viewBox="0 0 512 512" fill="currentColor">
                                    <path d="M208 0H332.1c12.7 0 24.9 5.1 33.9 14.1l67.9 67.9c9 9 14.1 21.2 14.1 33.9V336c0 26.5-21.5 48-48 48H208c-26.5 0-48-21.5-48-48V48c0-26.5 21.5-48 48-48zM48 128h80v64H64V448H256V416h64v48c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V176c0-26.5 21.5-48 48-48z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
@endunless
