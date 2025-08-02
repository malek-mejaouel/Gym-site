
  document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.delete-user-btn');

    buttons.forEach(button => {
      button.addEventListener('click', function () {
        const userId = this.dataset.id;

        if (confirm(`Are you sure you want to delete this user ${userId}?`))
            {
          fetch(`/delete-user/${userId}`, {
            method: 'DELETE',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              this.closest('tr').remove(); // Remove table row
            } else {
              alert('Failed to delete user.');
            }
          });
        }
      });
    });
  });

