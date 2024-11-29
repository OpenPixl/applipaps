const customerMaidenname = document.getElementById('reco_customerMaiden')
const civility = document.querySelectorAll('input[name="reco[customerCivility]"]');

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