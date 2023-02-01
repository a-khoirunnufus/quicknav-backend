window.addEventListener('DOMContentLoaded', function() {

  let selectedFile = {};

  document.querySelectorAll('input[name="file_id"]').forEach((elm) => {
    elm.addEventListener('change', function() {
      if(this.checked) {
        selectedFile[this.value] = true;
      } else {
        delete selectedFile[this.value];
      }
      document.querySelector('#selected-file-count').innerText = Object.keys(selectedFile).length;
    });
  });

  const formSelectFiles1 = document.querySelector('#form-select-files-1');
  document.querySelector('#submit-selected-files-1').addEventListener('click', () => {
    let selectedFileText = Object.keys(selectedFile);
    selectedFileText = selectedFileText.join(',');
    document.querySelector('#form-select-files-1 input[name="selected_files"]').value = selectedFileText;
    formSelectFiles1.submit();
  });


  // button submit final items click
  document.querySelector('#btn-submit-final-targets').addEventListener('click', () => {
    let finalTargets = [];

    // get all file frequency values
    const freqInputElmns = document.querySelectorAll('#table-mapping-file input[name="file_freq"]'); 
    freqInputElmns.forEach((elm) => {
      const fileId = elm.getAttribute('data-file-id');
      const frequency = elm.value;
      finalTargets.push(`${fileId}@${frequency}`);
    });
    
    // convert to string format to send in form
    const finalTargetsString = finalTargets.join(',');
    document.querySelector('#form-set-final-targets input[name="final_targets"]').value = finalTargetsString;

    // submitting form
    document.querySelector('#form-set-final-targets').submit();
  })

  // OPEN TARGET VALIDATION MODAL
  document.querySelectorAll('.btn-open-modal-target-validation').forEach((elm) => {
    elm.addEventListener('click', function() {
      console.log('open modal');
      const targetId = this.getAttribute('data-target-id');
      const targetName = this.getAttribute('data-target-name');
      const targetDepth = this.getAttribute('data-target-depth');
      const targetPathToFile = this.getAttribute('data-target-path_to_file');
      const targetViewedByMeTime = this.getAttribute('data-target-viewed_by_me_time');
      const targetFrequency = this.getAttribute('data-target-frequency');
      const targetStatus = this.getAttribute('data-target-status');
      const targetDescription = this.getAttribute('data-target-description');
  
      document.querySelector('#modal-target-validation #modal-target-name').innerText = targetName;
      document.querySelector('#modal-target-validation #modal-target-depth').innerText = targetDepth;
      document.querySelector('#modal-target-validation #modal-target-path_to_file').innerText = targetPathToFile;
      document.querySelector('#modal-target-validation #modal-target-viewed_by_me_time').innerText = targetViewedByMeTime;
      document.querySelector('#modal-target-validation #modal-target-frequency').innerText = targetFrequency;
      document.querySelector('#modal-target-validation #modal-target-status').innerText = targetStatus;
      document.querySelector('#modal-target-validation #modal-target-description').innerText = targetDescription;
      document.querySelector('#modal-target-validation input[name="target_id"]').value = targetId;
    });
  })

  // TARGET VALIDAITON
  document.querySelectorAll('.btn-target-validation').forEach((elm) => {
    elm.addEventListener('click', function() {
      const value = this.getAttribute('data-value');
      document.querySelector('#modal-target-validation input[name="status"').value = value;
      document.querySelector('#modal-target-validation #form-target-validation').submit();
    });
  })

});