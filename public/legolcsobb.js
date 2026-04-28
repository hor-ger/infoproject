document.getElementById('kereso_mezo').addEventListener('input', function() {
    let keresendo = this.value;

    if (keresendo.length >= 0) {
        fetch('kereso.php?kereses=' + encodeURIComponent(keresendo))
            .then(response => response.text())
            .then(data => {
                document.getElementById('eredmenyek').innerHTML = data;
            })
            .catch(error => console.error('Hiba:', error));
    }
});

window.onload = () => {
    document.getElementById('kereso_mezo').dispatchEvent(new Event('input'));
};