// Use sortable to create a draggable interface
var sortable = new Sortable(rankingList, {
    animation: 150
});

function getOrder() {

    let preferences = [];
    // Gets current order of preferences to then post
    document.querySelectorAll("#rankingList li").forEach((item, index) => {
        preferences.push({
            project_id: item.getAttribute("data-id"),
            order: index+1
        })
    });

    // Saves order of preferences in DB for students
    fetch("../PHP/savePreferences.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(preferences) 
    })
    .then(response => response.text())
    .then(data => {
        console.log("Server response:", data);
    })
    .catch(error => console.error(error));
}