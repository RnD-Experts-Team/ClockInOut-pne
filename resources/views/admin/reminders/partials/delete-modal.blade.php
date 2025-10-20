<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-trash"></i> Delete Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to delete this reminder? This action cannot be undone.
                <div class="text-danger small d-none" id="deleteError"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('deleteModal');
        const btn = document.getElementById('confirmDelete');
        btn?.addEventListener('click', () => {
            const id = modal.getAttribute('data-reminder-id');
            fetch(`/reminders/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "{{ route('reminders.index') }}";
                    } else {
                        document.getElementById('deleteError').classList.remove('d-none');
                        document.getElementById('deleteError').textContent = data.message || 'Delete failed.';
                    }
                })
                .catch(() => {
                    document.getElementById('deleteError').classList.remove('d-none');
                    document.getElementById('deleteError').textContent = 'Network error.';
                });
        });
    });
</script>
