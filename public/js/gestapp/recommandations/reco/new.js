import { showToast, findCommunes } from '../../../component/function.js';

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