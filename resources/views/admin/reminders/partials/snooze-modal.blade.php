<div class="modal fade" id="snoozeModal" tabindex="-1" aria-labelledby="snoozeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="snoozeModalLabel"><i class="fas fa-clock"></i> Snooze Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="snoozeForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Snooze until</label>
                        <input type="datetime-local" class="form-control" name="snooze_until" required>
                        <div class="form-text">Pick a new date & time to be reminded.</div>
                    </div>
                </form>
                <div class="text-danger small d-none" id="snoozeError"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmSnooze">Snooze</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('snoozeModal');
        const btn = document.getElementById('confirmSnooze');
        btn?.addEventListener('click', () => {
            const id = modal.getAttribute('data-reminder-id');
            const form = document.getElementById('snoozeForm');
            const body = { snooze_until: form.snooze_until.value };
            fetch(`/reminders/${id}/snooze`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(body)
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        document.getElementById('snoozeError').classList.remove('d-none');
                        document.getElementById('snoozeError').textContent = data.message || 'Failed to snooze.';
                    }
                })
                .catch(() => {
                    document.getElementById('snoozeError').classList.remove('d-none');
                    document.getElementById('snoozeError').textContent = 'Network error.';
                });
        });
    });
</script>
