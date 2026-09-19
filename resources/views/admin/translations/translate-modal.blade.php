{{-- App Translations: translate every missing string in small batches until done. --}}
<div class="modal fade" id="appTranslateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Translate App Text</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-translate-close></button>
            </div>
            <div class="modal-body">
                <label class="form-label">App</label>
                <select class="form-select mb-3" data-translate-app>
                    <option value="">All apps</option>
                    @foreach(config('localization.apps', []) as $appKey => $appLabel)
                        <option value="{{ $appKey }}" @selected(request('app') === $appKey)>{{ $appLabel }}</option>
                    @endforeach
                </select>

                <p class="small text-muted mb-3">
                    Missing app text is copied from the website's translations first, then from another app,
                    and only the rest is sent to Google. Existing and hand-corrected translations are never changed,
                    so you can stop and press Translate again later.
                </p>

                <div class="progress mb-2" style="height: 10px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" data-translate-bar></div>
                </div>
                <div class="small" data-translate-status>Ready.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-translate-stop disabled>Stop</button>
                <button type="button" class="btn btn-success" data-translate-start>
                    <i class="iconoir-translate me-1"></i>Start Translating
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('appTranslateModal');
        if (!modal) return;

        const url = @json(route('admin.translations.translate-batch'));
        const token = @json(csrf_token());
        const startBtn = modal.querySelector('[data-translate-start]');
        const stopBtn = modal.querySelector('[data-translate-stop]');
        const appSelect = modal.querySelector('[data-translate-app]');
        const bar = modal.querySelector('[data-translate-bar]');
        const status = modal.querySelector('[data-translate-status]');
        let running = false;
        let changed = false;

        const setRunning = (value) => {
            running = value;
            startBtn.disabled = value;
            appSelect.disabled = value;
            stopBtn.disabled = !value;
        };

        startBtn.addEventListener('click', async () => {
            setRunning(true);
            let done = 0;
            let fromWebsite = 0;
            let fromApps = 0;
            let fromGoogle = 0;
            let failed = 0;
            status.textContent = 'Starting...';

            while (running) {
                let result;
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ app: appSelect.value || null }),
                    });
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    result = await response.json();
                } catch (error) {
                    status.textContent = 'Stopped: ' + error.message + '. Press Start to continue.';
                    break;
                }

                const progressed = result.translated + result.website + result.reused;
                done += progressed;
                fromWebsite += result.website;
                fromApps += result.reused;
                fromGoogle += result.translated;
                failed += result.failed;
                changed = changed || progressed > 0;

                const total = done + result.remaining;
                const breakdown = 'website ' + fromWebsite + ', other app ' + fromApps + ', Google ' + fromGoogle;
                bar.style.width = (total === 0 ? 100 : Math.round(done / total * 100)) + '%';
                status.textContent = 'Saved ' + done + ' (' + breakdown + '), remaining ' + result.remaining + (failed ? ', failed ' + failed : '') + '.';

                if (result.remaining === 0) {
                    status.textContent = 'Done. ' + done + ' translations saved (' + breakdown + ').';
                    break;
                }

                if (progressed === 0) {
                    status.textContent = 'Translator is not responding right now (' + result.remaining + ' remaining). Try again later.';
                    break;
                }
            }

            setRunning(false);
        });

        stopBtn.addEventListener('click', () => {
            running = false;
            status.textContent = 'Stopping after the current batch...';
        });

        modal.addEventListener('hidden.bs.modal', () => {
            running = false;
            if (changed) window.location.reload();
        });
    })();
</script>
