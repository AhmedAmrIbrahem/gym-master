{{-- Same exercise_id / search endpoint on every device. Mobile presents a viewport-aware sheet. --}}
<div class="gym-exercise-picker relative" data-search-url="{{ route('programs.exercises.search') }}"
     x-data="{
        open: false, mobile: false, flip: false, search: '', selected: '', selectedName: '',
        items: [], loading: false, error: false, requestId: 0,
        viewportHeight: 0, viewportTop: 0, onVisualResize: null,
        init() {
            this.mobile = window.matchMedia('(max-width: 767px)').matches;
            this.updateViewport();
            this.onVisualResize = () => this.updateViewport();
            window.visualViewport?.addEventListener('resize', this.onVisualResize);
            window.visualViewport?.addEventListener('scroll', this.onVisualResize);
        },
        destroy() {
            window.visualViewport?.removeEventListener('resize', this.onVisualResize);
            window.visualViewport?.removeEventListener('scroll', this.onVisualResize);
            document.body.classList.remove('gym-exercise-dialog-open');
        },
        updateViewport() {
            this.viewportHeight = Math.round(window.visualViewport?.height || window.innerHeight);
            this.viewportTop = Math.round(window.visualViewport?.offsetTop || 0);
        },
        onResize() {
            const wasMobile = this.mobile;
            this.mobile = window.matchMedia('(max-width: 767px)').matches;
            this.updateViewport();
            if (this.open && wasMobile !== this.mobile) {
                document.body.classList.toggle('gym-exercise-dialog-open', this.mobile);
                this.$nextTick(() => this.focusSearch());
            }
        },
        focusSearch() {
            const field = this.mobile ? this.$refs.mobileSearch : this.$refs.desktopSearch;
            field?.focus({ preventScroll: true });
        },
        show() {
            this.mobile = window.matchMedia('(max-width: 767px)').matches;
            this.updateViewport();
            if (!this.mobile) {
                const rect = this.$refs.trigger.getBoundingClientRect();
                this.flip = window.innerHeight - rect.bottom < 285 && rect.top > 285;
            }
            this.open = true;
            this.search = '';
            this.items = [];
            document.body.classList.toggle('gym-exercise-dialog-open', this.mobile);
            this.load();
            this.$nextTick(() => this.focusSearch());
        },
        hide(returnFocus = false) {
            this.open = false;
            this.requestId++;
            this.loading = false;
            document.body.classList.remove('gym-exercise-dialog-open');
            if (returnFocus) this.$nextTick(() => this.$refs.trigger?.focus({ preventScroll: true }));
        },
        choose(exercise) {
            this.selected = String(exercise.id);
            this.selectedName = exercise.name;
            this.hide(true);
        },
        async load() {
            const id = ++this.requestId;
            this.loading = true;
            this.error = false;
            try {
                const url = new URL(this.$refs.trigger.closest('[data-search-url]').dataset.searchUrl, window.location.href);
                url.searchParams.set('q', this.search.trim().slice(0, 80));
                const response = await fetch(url.toString(), {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin', cache: 'no-store'
                });
                if (!response.ok) throw new Error('Search failed');
                const data = await response.json();
                if (id !== this.requestId || !this.open) return;
                this.items = Array.isArray(data.items) ? data.items : [];
            } catch (error) {
                if (id !== this.requestId || !this.open) return;
                this.items = [];
                this.error = true;
            } finally {
                if (id === this.requestId) this.loading = false;
            }
        },
        groups() {
            const grouped = new Map();
            for (const exercise of this.items) {
                const muscle = exercise.target_muscle || 'غير محدد';
                if (!grouped.has(muscle)) grouped.set(muscle, []);
                grouped.get(muscle).push(exercise);
            }
            return [...grouped.entries()].map(([muscle, exercises]) => ({ muscle, exercises }));
        }
     }"
     @resize.window="onResize()"
     @keydown.escape.window="if (open) hide(true)"
     @click.outside="if (open && !mobile) hide()">

    <input type="hidden" name="exercise_id" :value="selected" required>
    <button x-ref="trigger" type="button" @click="open ? hide() : show()" :aria-expanded="open.toString()"
            :aria-haspopup="mobile ? 'dialog' : 'listbox'"
            class="gym-exercise-trigger w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-xl px-3 py-3 text-right flex items-center justify-between gap-3">
        <span x-text="selectedName || 'اختر تمرين من القائمة...'" class="min-w-0 truncate"></span>
        <span aria-hidden="true">▾</span>
    </button>

    {{-- Desktop popover: stays inside its card and flips upward near the viewport edge. --}}
    <div x-show="open && !mobile" x-cloak
         :class="{ 'gym-exercise-popover-up': flip }"
         class="gym-exercise-popover absolute inset-x-0 z-40 mt-2 rounded-xl border border-gray-700 bg-gray-800 shadow-xl p-2"
         role="group" aria-label="اختيار تمرين">
        <input x-ref="desktopSearch" type="search" x-model="search" @input.debounce.250ms="load()"
               placeholder="ابحث عن اسم تمرين أو عضلة..." aria-label="ابحث عن تمرين" autocomplete="off"
               class="gym-exercise-search w-full mb-2 bg-gray-900 border-gray-700 text-white rounded-lg text-sm">
        <div class="gym-exercise-results max-h-56 overflow-y-auto overscroll-contain space-y-1" role="listbox" aria-label="نتائج بحث التمارين">
            <p x-show="loading" class="text-center text-xs text-gray-400 py-3" role="status">جاري البحث عن التمارين...</p>
            <div x-show="error" class="text-center py-3">
                <p class="text-xs text-red-400 mb-2">تعذر تحميل التمارين، حاول مرة تانية.</p>
                <button type="button" @click="load()" class="text-xs text-blue-400 underline">إعادة المحاولة</button>
            </div>
            <template x-for="group in groups()" :key="group.muscle">
                <div class="gym-exercise-group">
                    <div class="gym-exercise-group-title text-xs text-blue-400 font-bold px-2 pt-2 pb-1" x-text="group.muscle"></div>
                    <template x-for="exercise in group.exercises" :key="exercise.id">
                        <button type="button" role="option" :aria-selected="selected === String(exercise.id)"
                                @click="choose(exercise)"
                                class="gym-exercise-option block w-full text-right rounded-lg text-sm px-3 py-2 text-gray-200 hover:bg-blue-600/20"
                                x-text="exercise.name"></button>
                    </template>
                </div>
            </template>
            <p x-show="!loading && !error && items.length === 0" class="text-center text-xs text-gray-400 py-3">مفيش تمارين مطابقة للبحث</p>
            <p x-show="!loading && !error && items.length === 40" class="text-center text-xs text-gray-400 py-2">أول 40 نتيجة — اكتب اسم التمرين عشان تحدد بحثك</p>
        </div>
    </div>

    {{-- The mobile sheet is teleported outside program cards to avoid clipping or nested scrollbars. --}}
    <template x-teleport="body">
        <div x-show="open && mobile" x-cloak class="gym-exercise-mobile-layer"
             :class="{ 'gym-exercise-mobile-layer-compact': viewportHeight < 430 }"
             :style="{ top: viewportTop + 'px', height: viewportHeight + 'px' }">
            <button type="button" class="gym-exercise-mobile-backdrop" @click="hide(true)" aria-label="إغلاق اختيار التمرين"></button>
            <section class="gym-exercise-mobile-sheet" role="dialog" aria-modal="true" aria-label="اختيار تمرين من بنك التمارين">
                <div class="gym-exercise-mobile-handle" aria-hidden="true"></div>
                <div class="gym-exercise-mobile-heading">
                    <div>
                        <p class="gym-exercise-mobile-title">اختيار تمرين</p>
                        <p class="gym-exercise-mobile-subtitle">ابحث بالاسم أو العضلة المستهدفة</p>
                    </div>
                    <button type="button" class="gym-exercise-mobile-close" @click="hide(true)" aria-label="إغلاق قائمة التمارين">✕</button>
                </div>
                <input x-ref="mobileSearch" type="search" x-model="search" @input.debounce.250ms="load()"
                       placeholder="اسم التمرين أو العضلة..." aria-label="ابحث عن تمرين" autocomplete="off"
                       class="gym-exercise-search gym-exercise-mobile-search w-full rounded-xl">
                <div class="gym-exercise-results gym-exercise-mobile-results" role="listbox" aria-label="نتائج بحث التمارين">
                    <p x-show="loading" class="gym-exercise-mobile-message" role="status">جاري البحث عن التمارين...</p>
                    <div x-show="error" class="gym-exercise-mobile-message">
                        <p>تعذر تحميل التمارين. حاول مرة تانية.</p>
                        <button type="button" @click="load()" class="gym-exercise-mobile-retry">إعادة المحاولة</button>
                    </div>
                    <template x-for="group in groups()" :key="group.muscle">
                        <div class="gym-exercise-group">
                            <div class="gym-exercise-group-title" x-text="group.muscle"></div>
                            <template x-for="exercise in group.exercises" :key="exercise.id">
                                <button type="button" role="option" :aria-selected="selected === String(exercise.id)"
                                        @click="choose(exercise)" class="gym-exercise-option gym-exercise-mobile-option"
                                        x-text="exercise.name"></button>
                            </template>
                        </div>
                    </template>
                    <p x-show="!loading && !error && items.length === 0" class="gym-exercise-mobile-message">مفيش تمارين مطابقة للبحث</p>
                    <p x-show="!loading && !error && items.length === 40" class="gym-exercise-mobile-message">أول 40 نتيجة — اكتب جزء من الاسم عشان تحدد بحثك</p>
                </div>
            </section>
        </div>
    </template>
</div>
