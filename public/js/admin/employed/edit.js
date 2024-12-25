// import {modal_tw} from '../../Component/tailwindComponent';

const openModalWarning = document.querySelectorAll('a.openModalWarning');
const modal_closed = document.querySelectorAll('button.modal_closed')
const modalWarning = document.getElementById('modalWarning')
const btnSupprProfil = document.getElementById('btnSupprProfil')

btnSupprProfil.addEventListener('click', function(event){
    event.preventDefault();
    openModal;
})

function openModal(event){
    event.preventDefault();
    let url = this.href;
    document.getElementById('modalWarning').style.display = 'block'
    document.getElementsByTagName('body')[0].classList.add('overflow-y-hidden')
    modalWarning.querySelector('#modal_body #modal_body_title').textContent = "Vous êtes sur le point de supprimer le document. Etes-vous sûr ?";
    modalWarning.querySelector('#modal_body .validModal').href = url;
    modalWarning.querySelector('#modal_body .validModal').addEventListener('click', validModal);
    reloadEvents();
}

function closeModal(){
    document.getElementById('modalWarning').style.display = 'none'
    document.getElementsByTagName('body')[0].classList.remove('overflow-y-hidden')
}

function validModal(event){
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