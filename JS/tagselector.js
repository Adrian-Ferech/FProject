let expanded = false;
// Legacy feature to toggle old dropdowns
function toggleDropdown() {
    const checkboxes = document.getElementById("checkboxes");
    checkboxes.style.display = expanded ? "none" : "block";
    expanded = !expanded;
}

document.querySelectorAll('input[name="tags[]"]').forEach(cb => {
    cb.addEventListener('change', updateSelectedTags);
});

function updateSelectedTags() {
    const selected = [];
    document.querySelectorAll('input[name="tags[]"]:checked').forEach(cb => {
        selected.push(cb.parentElement.textContent.trim());
    });

    document.getElementById("selectedTags").textContent =
        selected.length ? selected.join(", ") : "Select tags";
}