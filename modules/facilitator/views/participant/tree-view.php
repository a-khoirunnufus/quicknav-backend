<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tree View</title>
  <style>
    /* Remove default bullets */
    ul, #myUL {
      list-style-type: none;
    }

    /* Remove margins and padding from the parent ul */
    #myUL {
      margin: 0;
      padding: 0;
    }

    /* Style the caret/arrow */
    .caret {
      cursor: pointer;
      user-select: none; /* Prevent text selection */
    }

    /* Create the caret/arrow with a unicode, and style it */
    .caret::before {
      content: "\25B6";
      color: black;
      display: inline-block;
      margin-right: 6px;
    }

    /* Rotate the caret/arrow icon when clicked on (using JavaScript) */
    .caret-down::before {
      transform: rotate(90deg);
    }

    /* Hide the nested list */
    .nested {
      display: none;
    }

    /* Show the nested list when the user clicks on the caret/arrow (with JavaScript) */
    .active {
      display: block;
    }

    span { color: gainsboro }
    .level-1 { color: #ff0000 }
    .level-2 { color: #ff7f00 }
    .level-3 { color: #ffff00 }
    .level-4 { color: #00ff00 }
    .level-5 { color: #0000ff }
    .level-6 { color: #9400d3 }
    .level-7 { color: #4b0082 }
    .level-8 { color: #000000 }
  </style>
</head>
<body>

<div style="display: flex; flex-direction: row; margin-bottom: 2rem; gap: 1rem;">
  <span class="level-1">Level 1<span>
  <span class="level-2">Level 2<span>
  <span class="level-3">Level 3<span>
  <span class="level-4">Level 4<span>
  <span class="level-5">Level 5<span>
  <span class="level-6">Level 6<span>
  <span class="level-7">Level 7<span>
  <span class="level-8">Level 8<span>
  <span>Other<span>
</div>

<ul id="myUL">
  <?= $html ?>
</ul>

<script>
window.addEventListener('DOMContentLoaded', function() {

  var toggler = document.getElementsByClassName("caret");
  var i;

  for (i = 0; i < toggler.length; i++) {
    toggler[i].addEventListener("click", function() {
      this.parentElement.querySelector(".nested").classList.toggle("active");
      this.classList.toggle("caret-down");
    });
  }

})
</script>

</body>
</html>