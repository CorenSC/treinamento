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
    dropdown();
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

function dropdown() {
    // Verifica o estado dos dropdowns salvos no localStorage
    const openDropdownIds = JSON.parse(localStorage.getItem('openDropdownIds')) || [];

    // Reabre todos os dropdowns que estavam abertos
    openDropdownIds.forEach(function (dropdownId) {
        $("#" + dropdownId).find('.hs-accordion-content').slideDown();
        $("#" + dropdownId).find('.hs-accordion-toggle').attr("aria-expanded", "true");
    });

    // Quando o vídeo é clicado
    $(".video-link").on("click", function (e) {
        // Pega o id do dropdown relacionado ao vídeo clicado
        const dropdownId = $(this).closest('.hs-accordion').attr('id');

        // Verifica se o dropdown já está na lista de abertos
        if (!openDropdownIds.includes(dropdownId)) {
            openDropdownIds.push(dropdownId); // Adiciona o dropdown à lista de abertos
        }

        // Salva a lista de dropdowns abertos no localStorage
        localStorage.setItem('openDropdownIds', JSON.stringify(openDropdownIds));
    });

    // Quando o dropdown é clicado (abrir ou fechar)
    $(".hs-accordion-toggle").on("click", function () {
        const dropdownId = $(this).closest('.hs-accordion').attr('id');

        // Verifica o estado atual (se está aberto ou fechado)
        const isExpanded = $(this).attr("aria-expanded") === "true";

        if (isExpanded) {
            // Se estava aberto e o usuário clicou, remove da lista
            const index = openDropdownIds.indexOf(dropdownId);
            if (index > -1) {
                openDropdownIds.splice(index, 1); // Remove o dropdown da lista de abertos
            }
        } else {
            // Se estava fechado e o usuário clicou, adiciona à lista
            if (!openDropdownIds.includes(dropdownId)) {
                openDropdownIds.push(dropdownId);
            }
        }

        // Atualiza o localStorage
        localStorage.setItem('openDropdownIds', JSON.stringify(openDropdownIds));
    });
}