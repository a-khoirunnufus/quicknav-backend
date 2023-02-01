window.addEventListener('DOMContentLoaded', function() {
  
  // ADD TASK
  document.querySelector('#btn-submit-add-form').addEventListener('click', () => {
    document.querySelector('#form-add-task').submit();
    console.log('submitting add form');
  });
  
  // UPDATE TASK
  document.querySelectorAll('.btn-open-edit-modal').forEach((elm) => {
    elm.addEventListener('click', function() {
      console.log('edit button clicked');
      const order = this.getAttribute('data-order');
      const code = this.getAttribute('data-code');
      const name = this.getAttribute('data-name');
      const interface = this.getAttribute('data-interface');
      const hintVisible = this.getAttribute('data-hint_visible');
      const isLock = this.getAttribute('data-is_lock');
      const taskId = this.getAttribute('data-task-id');
      
      document.querySelector('#form-update-task input[name="order"]').value = order;
      document.querySelector('#form-update-task input[name="code"]').value = code;
      document.querySelector('#form-update-task input[name="name"]').value = name;
      document.querySelector('#form-update-task input[name="interface"]').value = interface;
      document.querySelector('#form-update-task input[name="hint_visible"]').value = hintVisible;
      document.querySelector('#form-update-task input[name="is_lock"]').value = isLock;
      document.querySelector('#form-update-task input[name="task_id"]').value = taskId;
    });
  })
  document.querySelector('#btn-submit-update-form').addEventListener('click', () => {
    document.querySelector('#form-update-task').submit();
    console.log('submitting update form');
  });

  // DELETE TASK
  document.querySelectorAll('.btn-open-delete-modal').forEach((elm) => {
    elm.addEventListener('click', function() {
      console.log('delete button clicked');
      const taskId = this.getAttribute('data-task-id');
      document.querySelector('#form-delete-task input[name="task_id"]').value = taskId;
    });
  })
  document.querySelector('#btn-submit-delete-form').addEventListener('click', () => {
    document.querySelector('#form-delete-task').submit();
    console.log('submitting delete form');
  });

  // LOAD DEFAULT TASK
  document.querySelector('#btn-submit-load-default-form').addEventListener('click', () => {
    document.querySelector('#form-load-default-task').submit();
    console.log('submitting load default form');
  });
});

