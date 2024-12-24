import { showToast } from '../../../component/function.js';

const test_notification = document.getElementById('test_notification');

test_notification.addEventListener('click', function(event){
    showToast('Bienvenue sur le site !', 'info');
});