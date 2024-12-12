const customerMaidenname = document.getElementById('reco_customerMaiden')
const civility = document.querySelectorAll('input[name="reco[customerCivility]"]');

let city = document.querySelector('#reco_propertyCity');
let zipcode = document.querySelector('#reco_propertyZipcode');
let select = document.querySelector('#selectcity');

// -- visuel sur le nom de jeune fille --
let valcivility = document.querySelector('input[name=reco\\[customerCivility\\]]:checked').value;
if (valcivility > 1){
    customerMaidenname.classList.remove('hidden');
}
const radioButtonsCivility = document.querySelectorAll('input[name=reco\\[customerCivility\\]]');
console.log(radioButtonsCivility);
radioButtonsCivility.forEach(function(radio) {
    radio.addEventListener("change", function() {
        if (parseInt(this.value) === 2) {
            customerMaidenname.classList.remove('hidden');
        } else if (parseInt(this.value) === 1){
            customerMaidenname.classList.add('hidden');
        }
    });
});

zipcode.addEventListener('input', function(event){
    findCommunes(city, zipcode, select);
});
select.addEventListener('change', function (event){
    let value = this.value.split(' ');
    zipcode.value = value[0];
    city.value = value[2].toUpperCase();
});

function removeOptions(selectElement) {
    var i, L = selectElement.options.length - 1;
    for(i = L; i >= 0; i--) {
        selectElement.remove(i);
    }
}

// Fonction pour trouver les communes à partir du code postal
function findCommunes(City, Zipcode, Select) {
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