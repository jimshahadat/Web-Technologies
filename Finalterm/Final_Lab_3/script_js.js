function loadData() {
    var xhr = new XMLHttpRequest();

    document.getElementById("loading").innerHTML = "Loading...";
    document.getElementById("result").innerHTML = "";

    xhr.open("GET", "data.php", true);

    xhr.onload = function () {
        document.getElementById("loading").innerHTML = "";

        if (xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);

            document.getElementById("result").innerHTML =
                `<div class="card">
                    <strong>Name:</strong> ${data.name}<br>
                    <strong>Age:</strong> ${data.age}<br>
                    <strong>City:</strong> ${data.city}
                </div>`;
        } else {
            document.getElementById("result").innerHTML = "Error loading data";
        }
    };

    xhr.send();
}

function clearData() {
    document.getElementById("result").innerHTML = "";
}