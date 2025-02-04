const Maidenname = document.getElementById('registration_form2_maidenName')
const civility = document.querySelectorAll('input[name="registration_form2[civility]"]');

let valcivility = document.querySelector('input[name=registration_form2\\[civility\\]]:checked').value;
if (valcivility > 1){
    Maidenname.classList.remove('hidden');
}
const radioButtonsCivility = document.querySelectorAll('input[name=registration_form2\\[civility\\]]');
radioButtonsCivility.forEach(function(radio) {
    radio.addEventListener("change", function() {
        if (parseInt(this.value) === 2) {
            Maidenname.classList.remove('hidden');
        } else if (parseInt(this.value) === 1){
            Maidenname.classList.add('hidden');
        }
    });
});