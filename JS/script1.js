// Toggles the dropdown when pressing button
function myFunction() {
  document.getElementById("dropdown-menu").classList.toggle("show");
}

function filterFunction() {
  const input = document.getElementById("myInput"); // find user input
  const filter = input.value.toUpperCase(); // turn input into uppercase
  const div = document.getElementById("dropdown-menu"); 
  const a = div.getElementsByTagName("a"); // get each element of the dropdown
  for (let i = 0; i < a.length; i++) {
    txtValue = a[i].textContent || a[i].innerText;
    if (txtValue.toUpperCase().indexOf(filter) > -1) { // hide each element if shown and opposite if false
      a[i].style.display = "";
    } else {
      a[i].style.display = "none";
    }
  }
}