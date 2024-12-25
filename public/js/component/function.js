function removeOptions(selectElement) {
    var i, L = selectElement.options.length - 1;
    for(i = L; i >= 0; i--) {
        selectElement.remove(i);
    }
}
// Fonction pour trouver les communes à partir du code postal
export function findCommunes(City, Zipcode, Select) {
    if (Zipcode.value.length === 5) {
        let coord = Zipcode.value;
        let xhr = new XMLHttpRequest();

        xhr.open('GET', 'https://apicarto.ign.fr/api/codes-postaux/communes/' + coord, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let features = JSON.parse(xhr.responseText);
                    removeOptions(Select);

                    features.forEach((element) => {
                        let name = element['codePostal'] + " - " + element['nomCommune'];
                        let OptSelect = new Option(name.toUpperCase(), name.toUpperCase(), false, true);
                        Select.options.add(OptSelect);
                    });

                    if (Select.options.length === 1) {
                        let value = Select.value.split(' ');
                        Zipcode.value = value[0];
                        City.value = value[2].toUpperCase();
                    } else {
                        let value = Select.value.split(' ');
                        Zipcode.value = value[0];
                        City.value = value[2].toUpperCase();
                    }
                } else {
                    console.error('Erreur lors de la requête AJAX :', xhr.statusText);
                }
            }
        };

        xhr.send();
    }
}

export function showToast(message, type = 'info', duration = 5000) {
    // Crée un événement personnalisé
    window.dispatchEvent(
        new CustomEvent('toast', {
            detail: { message, type, duration },
        })
    );
}