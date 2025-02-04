// import {modal_tw} from '../../Component/tailwindComponent';

const openModalWarning = document.querySelectorAll('a.openModalWarning');
const modal_closed = document.querySelectorAll('a.modal_closed')
const modalWarning = document.getElementById('modalWarning')
const EmployedMaidenname = document.getElementById('employed_maidenName')
const civility = document.querySelectorAll('input[name="employed[civility]"]');

// -- visuel sur le nom de jeune fille --
let valcivility = document.querySelector('input[name=employed\\[civility\\]]:checked').value;
if (valcivility > 1){
    EmployedMaidenname.classList.remove('hidden');
}
const radioButtonsCivility = document.querySelectorAll('input[name=employed\\[civility\\]]');
radioButtonsCivility.forEach(function(radio) {
    radio.addEventListener("change", function() {
        if (parseInt(this.value) === 2) {
            EmployedMaidenname.classList.remove('hidden');
        } else if (parseInt(this.value) === 1){
            EmployedMaidenname.classList.add('hidden');
        }
    });
});

function openModal(event){
    event.preventDefault();
    let url = this.href;
    let opt = this.getAttribute('data-tw-content');
    let crud = opt.split('-')[0];
    document.getElementById('modalWarning').style.display = 'block'
    document.getElementsByTagName('body')[0].classList.add('overflow-y-hidden')
    if(crud === 'NEW_MESS'){
        axios
            .post(url)
            .then(function(response){
                modalWarning.querySelector('#modal_header_title').innerHTML = 'Ecrire un message'
                modalWarning.querySelector('#modal_body #modal_body_icon').innerHTML = ''
                modalWarning.querySelector('#modal_body #modal_body_title').innerHTML = '<h3 class="text-sm font-normal text-gray-500">Votre contenu</h3>'
                modalWarning.querySelector('#modal_body #modal_body_text').innerHTML = response.data.formView
                modalWarning.querySelector('#modal_footer .validModal').textContent = 'Envoyer'
                modalWarning.querySelector('#modal_footer .validModal').addEventListener('click', validModal);
                modalWarning.querySelector('#modal_footer .validModal').setAttribute('data-tw-content', 'SUBMIT');
            })
            .catch(function(error){
                console.log(error)
            });
        reloadEvents();
    }else if(crud === "DEL"){
        modalWarning.querySelector('#modal_body #modal_body_title').innerHTML = ''
        modalWarning.querySelector('#modal_body #modal_body_icon').innerHTML =
            '<svg class="w-10 h-10 text-amber-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"\n' +
            'xmlns="http://www.w3.org/2000/svg">\n' +
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"\n' +
            'd="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>\n' +
            '</svg>'
        modalWarning.querySelector('#modal_body #modal_body_text').innerHTML = '<h3 class="text-sm font-normal text-gray-500">Vous êtes sur le point de supprimer le document. Etes-vous sûr ?</h3>';
        modalWarning.querySelector('#modal_footer .validModal').href = url;
        modalWarning.querySelector('#modal_footer .validModal').addEventListener('click', validModal);
        reloadEvents();
    }else if(crud === "DEL_IBAN"){
        modalWarning.querySelector('#modal_body #modal_body_title').innerHTML = '<h3 class="text-sm font-normal text-gray-500">Vous êtes sur le point de supprimer le document. Etes-vous sûr ?</h3>';
        modalWarning.querySelector('#modal_footer .validModal').href = url;
        modalWarning.querySelector('#modal_footer .validModal').addEventListener('click', validModal);
        reloadEvents();
    }

}

function closeModal(){
    modalWarning.querySelector('#modal_body #modal_body_title').innerHTML = ''
    modalWarning.querySelector('#modal_body #modal_body_icon').innerHTML = ''
    document.getElementById('modalWarning').style.display = 'none'
    document.getElementsByTagName('body')[0].classList.remove('overflow-y-hidden')
}

function validModal(event){
    let opt = this.getAttribute('data-tw-content');
    let crud = opt.split('-')[0];
    if(crud === "LINK"){
        event.preventDefault();
        let url = this.href;
        document.getElementById('modalWarning').style.display = 'none'
        document.getElementsByTagName('body')[0].classList.remove('overflow-y-hidden')
        axios
            .post(url)
            .then(function(response){
                if(response.data.type === 'ci'){
                    document.getElementById('addCi').innerHTML = response.data.view
                }
                else if(response.data.type === 'avatar'){
                    document.getElementById('addAvatar').innerHTML = response.data.view
                }
                else if(response.data.type === 'iban'){
                    document.getElementById('iban').innerHTML = response.data.view
                }
            })
            .catch(function(error){
                console.log(error)
            })
    }else if(crud === "SUBMIT"){
        let form = document.getElementById('formReco');
        let action = form.action;
        let data = new FormData(form);
        axios
            .post(action, data)
            .then(function(response){
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: response.data.message,
                        type: 'success',
                        duration: '5000',
                    },
                }));
                closeModal();
                reloadEvents;
            })
            .catch(function(error){
                console.log(error);
            })
        ;
    }

}

function reloadEvents(){
    openModalWarning.forEach(function(link){
        link.addEventListener('click', openModal);
    })
    modal_closed.forEach(function(button){
        button.addEventListener('click', closeModal);
    })
}

reloadEvents();