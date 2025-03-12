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

    updateSlidesQty();
})

function updateSlidesQty() {
    const isMobile = window.innerWidth < 768; // Define o breakpoint para mobile

    // Itera sobre todos os carrosséis
    $('[data-hs-carousel]').each(function () {
        const $carousel = $(this);
        const videoCount = $carousel.find('.hs-carousel-slide').length; // Conta o número de vídeos no carrossel

        // Define slidesQty com base no número de vídeos e no tamanho da tela
        const slidesQty = videoCount === 1 ? 1 : isMobile ? 1 : 2;

        // Atualiza o atributo data-hs-carousel
        $carousel.attr('data-hs-carousel', JSON.stringify({
            loadingClasses: "opacity-0",
            isInfiniteLoop: true,
            slidesQty: slidesQty, // Usa a variável slidesQty corretamente
            isDraggable: true
        }));
    });
}