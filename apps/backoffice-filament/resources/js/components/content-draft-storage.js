const STORAGE_KEY = 'hkgold-content-draft';
const SAVE_DEBOUNCE_MS = 2000;
const RESTORE_DELAY_MS = 350;

const TEXT_DRAFT_FIELDS = ['type', 'status', 'title', 'body_content', 'event_date'];

function contentDraftStorage(config) {
    return {
        isPersisted: config.isPersisted,
        storageKey: config.storageKey ?? STORAGE_KEY,
        draftSaved: false,
        intervalId: null,
        saveTimer: null,
        restored: false,
        commitHookRegistered: false,

        init() {
            if (this.isPersisted) {
                return;
            }

            this.registerCommitHook();
            this.scheduleRestore();
            this.registerUnloadSave();

            this.intervalId = window.setInterval(() => this.saveDraft(), 30000);
        },

        registerCommitHook() {
            if (this.commitHookRegistered || typeof Livewire === 'undefined') {
                return;
            }

            this.commitHookRegistered = true;

            Livewire.hook('commit', ({ component, succeed }) => {
                succeed(() => {
                    if (this.isPersisted) {
                        return;
                    }

                    if (component?.el?.contains(this.$el)) {
                        this.debouncedSave();
                    }
                });
            });
        },

        scheduleRestore() {
            const restore = () => this.restoreDraft();

            this.$nextTick(() => {
                window.requestAnimationFrame(() => {
                    window.setTimeout(restore, RESTORE_DELAY_MS);
                });
            });

            document.addEventListener('livewire:navigated', () => {
                if (! this.isPersisted) {
                    window.setTimeout(restore, RESTORE_DELAY_MS);
                }
            });

            window.addEventListener('content-draft-restore', () => {
                window.setTimeout(restore, RESTORE_DELAY_MS);
            });
        },

        registerUnloadSave() {
            const save = () => {
                if (this.isPersisted) {
                    return;
                }

                this.saveDraft({ sync: true });
            };

            window.addEventListener('pagehide', save);
            window.addEventListener('beforeunload', save);
        },

        debouncedSave() {
            window.clearTimeout(this.saveTimer);
            this.saveTimer = window.setTimeout(() => this.saveDraft(), SAVE_DEBOUNCE_MS);
        },

        async callWire(method, ...params) {
            if (! this.$wire) {
                return null;
            }

            if (typeof this.$wire[method] === 'function') {
                return this.$wire[method](...params);
            }

            if (typeof this.$wire.call === 'function') {
                return this.$wire.call(method, ...params);
            }

            return null;
        },

        pickTextFields(state) {
            if (! state || typeof state !== 'object') {
                return {};
            }

            return TEXT_DRAFT_FIELDS.reduce((carry, field) => {
                if (Object.prototype.hasOwnProperty.call(state, field)) {
                    carry[field] = state[field];
                }

                return carry;
            }, {});
        },

        hasMeaningfulState(state) {
            const textState = this.pickTextFields(state);
            const title = typeof textState.title === 'string' ? textState.title.trim() : '';
            const body = textState.body_content;
            const hasBody = body !== null
                && body !== undefined
                && body !== ''
                && body !== '<p></p>'
                && !(Array.isArray(body) && body.length === 0);

            return title !== '' || hasBody;
        },

        async saveDraft({ sync = false } = {}) {
            if (this.isPersisted) {
                return;
            }

            const run = async () => {
                try {
                    const state = await this.callWire('getDraftSnapshot');
                    const textState = this.pickTextFields(state);

                    if (! this.hasMeaningfulState(textState)) {
                        return;
                    }

                    localStorage.setItem(this.storageKey, JSON.stringify(textState));
                    this.draftSaved = true;
                } catch (error) {
                    console.error('Gagal menyimpan draf ke localStorage.', error);
                }
            };

            if (sync) {
                await run();

                return;
            }

            await run();
        },

        async restoreDraft() {
            if (this.isPersisted || this.restored) {
                return;
            }

            const raw = localStorage.getItem(this.storageKey);

            if (! raw) {
                return;
            }

            try {
                const state = JSON.parse(raw);
                const textState = this.pickTextFields(state);

                if (! this.hasMeaningfulState(textState)) {
                    return;
                }

                await this.callWire('restoreDraftSnapshot', textState);
                this.restored = true;
                this.draftSaved = true;
            } catch (error) {
                console.error('Gagal memulihkan draf dari localStorage.', error);
            }
        },

        destroy() {
            if (this.intervalId !== null) {
                window.clearInterval(this.intervalId);
            }

            if (this.saveTimer !== null) {
                window.clearTimeout(this.saveTimer);
            }
        },
    };
}

function registerContentDraftStorage() {
    window.Alpine.data('contentDraftStorage', contentDraftStorage);
}

if (window.Alpine) {
    registerContentDraftStorage();
}

document.addEventListener('alpine:init', registerContentDraftStorage);

export default contentDraftStorage;
