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

function escapeSelector(selector) {
    return selector.replace(/([ #;&,.+*~':"!^$[\]()=>|/])/g, '\\$1');
}

function dropdown() {
    const isHomePage = window.location.pathname === "/"; // Verifique se a URL da home está correta

    let openDropdownIds = JSON.parse(localStorage.getItem('openDropdownIds')) || [];

    if (isHomePage) {
        localStorage.removeItem('openDropdownIds');
        openDropdownIds = [];
    } else {
        openDropdownIds.forEach(function (dropdownId) {
            const escapedDropdownId = escapeSelector(dropdownId);
            $("#" + escapedDropdownId).find('.hs-accordion-content').slideDown();
            $("#" + escapedDropdownId).find('.hs-accordion-toggle').attr("aria-expanded", "true");
        });
    }

    // Quando o vídeo é clicado
    $(".video-link").on("click", function (e) {
        const dropdownId = $(this).closest('.hs-accordion').attr('id');

        if (!openDropdownIds.includes(dropdownId)) {
            openDropdownIds.push(dropdownId);
        }

        localStorage.setItem('openDropdownIds', JSON.stringify(openDropdownIds));
    });

    $(".hs-accordion-toggle").on("click", function () {
        const dropdownId = $(this).closest('.hs-accordion').attr('id');
        const isExpanded = $(this).attr("aria-expanded") === "true";

        if (isExpanded) {
            const index = openDropdownIds.indexOf(dropdownId);
            if (index > -1) {
                openDropdownIds.splice(index, 1);
            }
        } else {
            if (!openDropdownIds.includes(dropdownId)) {
                openDropdownIds.push(dropdownId);
            }
        }

        localStorage.setItem('openDropdownIds', JSON.stringify(openDropdownIds));
    });
}
