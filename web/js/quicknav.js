const items = document.querySelectorAll('.sv__item-wrapper');
items.forEach((elm) => {
  elm.addEventListener('click', function(e) {
    if(this.classList.contains('active')) {
      const fileId = this.getAttribute('data-id');
      console.log('navigate to:', fileId);
    }
    items.forEach((item) => {
      item.classList.remove('active');
    });
    this.classList.add('active');
  });
});