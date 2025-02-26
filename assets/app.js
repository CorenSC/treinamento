import './styles/app.css';
import 'preline/dist/preline.js';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'lodash/lodash.min';
import 'dropzone/dist/dropzone-min';
import $ from 'jquery'

$(() => {
    $('#close-toast').on('click', function() {
        $('#dismiss-toast').addClass('opacity-0 translate-x-5');
        setTimeout(function() {
            $('#dismiss-toast').remove();
        }, 300);
    });

    setTimeout(function() {
        $('#dismiss-toast').addClass('opacity-0 translate-x-5');
        setTimeout(function() {
            $('#dismiss-toast').remove();
        }, 300);
    }, 5000);
})