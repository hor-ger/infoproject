const keresoMezo = document.getElementById('kereso_mezo');
const kategoriaSzuro = document.getElementById('kategoria_szuro');

function frissitTalalatok() {
    let keresendo = keresoMezo.value;
    let kategoria = kategoriaSzuro.value;

    let url = 'kereso.php?kereses=' + encodeURIComponent(keresendo) + 
              '&kategoria=' + encodeURIComponent(kategoria);

    fetch(url)
        .then(response => response.text())
        .then(data => {
            document.getElementById('eredmenyek').innerHTML = data;
        })
        .catch(error => console.error('Hiba:', error));
}

keresoMezo.addEventListener('input', frissitTalalatok);

kategoriaSzuro.addEventListener('change', frissitTalalatok);

window.onload = frissitTalalatok;