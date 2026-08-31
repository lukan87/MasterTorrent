@if ($torrent->images->isNotEmpty())
<div class="card glass mb-4 border-0 shadow-sm rounded-4">
    <div class="card-body">

        <h5 class="fw-bold mb-3 text-primary">
            <i class="bi bi-images me-2"></i> Screenshots
        </h5>

        <div class="swiper screenshot-swiper">

            <div class="swiper-wrapper">

                @foreach ($torrent->images as $image)

                <div class="swiper-slide">

                    <div class="screenshot-card">

                        <img
                            src="{{ asset('storage/' . $image->path) }}"
                            class="screenshot-img"
                            loading="lazy"
                            data-full="{{ asset('storage/' . $image->path) }}"
                        >

                        <div class="screenshot-overlay">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </div>

                    </div>

                </div>

                @endforeach

            </div>

            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>

        </div>

    </div>
</div>
@endif

<style>


    .screenshot-card{
position:relative;
overflow:hidden;
border-radius:12px;
cursor:pointer;
}

.screenshot-img{
width:100%;
height:220px;
object-fit:cover;
transition:0.35s;
}

.screenshot-overlay{
position:absolute;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.45);
display:flex;
align-items:center;
justify-content:center;
opacity:0;
transition:0.25s;
}

.screenshot-overlay i{
font-size:28px;
color:white;
}

.screenshot-card:hover .screenshot-img{
transform:scale(1.08);
}

.screenshot-card:hover .screenshot-overlay{
opacity:1;
}

/* fullscreen viewer */

.image-viewer{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.95);
display:flex;
align-items:center;
justify-content:center;
z-index:9999;
}

.image-viewer img{
max-width:95%;
max-height:95%;
border-radius:10px;
}

.viewer-arrow{
position:absolute;
top:50%;
transform:translateY(-50%);
font-size:50px;
color:white;
padding:10px 20px;
cursor:pointer;
user-select:none;
opacity:0.7;
transition:0.2s;
}

.viewer-arrow:hover{
opacity:1;
}

.viewer-prev{
left:20px;
}

.viewer-next{
right:20px;
}

.viewer-img{
max-width:95%;
max-height:95%;
border-radius:10px;
transition:opacity .25s ease, transform .25s ease;
opacity:0;
transform:scale(0.95);
}

.image-viewer.show .viewer-img{
opacity:1;
transform:scale(1);
}

.viewer-counter{
position:absolute;
top:20px;
right:25px;
color:white;
font-size:16px;
background:rgba(0,0,0,0.6);
padding:6px 10px;
border-radius:6px;
}


.viewer-close{
position:absolute;
top:15px;
left:20px;
font-size:36px;
color:white;
cursor:pointer;
opacity:0.8;
transition:0.2s;
}

.viewer-close:hover{
opacity:1;
transform:scale(1.1);
}
</style>


<script>
document.addEventListener("DOMContentLoaded", function(){

  const cards = Array.from(document.querySelectorAll(".screenshot-card"));
const images = Array.from(document.querySelectorAll(".screenshot-img"));

cards.forEach((card,i)=>{
    card.addEventListener("click", function(e){

        // prevent swiper drag conflict
        if(e.target.closest(".swiper-button-next") || e.target.closest(".swiper-button-prev")) return;

        openViewer(i);
    });
});
    let current = 0;
    let viewer = null;

function openViewer(index){

    current = index;

    viewer = document.createElement("div");
    viewer.className = "image-viewer";

    const img = document.createElement("img");
    img.className = "viewer-img";

    const counter = document.createElement("div");
    counter.className = "viewer-counter";

    const close = document.createElement("div");
    close.className = "viewer-close";
    close.innerHTML = "&times;";

    const left = document.createElement("div");
    left.className = "viewer-arrow viewer-prev";
    left.innerHTML = "❮";

    const right = document.createElement("div");
    right.className = "viewer-arrow viewer-next";
    right.innerHTML = "❯";

    close.onclick = function(e){
        e.stopPropagation();
        closeViewer();
    };

    left.onclick = function(e){
        e.stopPropagation();
        prev();
    };

    right.onclick = function(e){
        e.stopPropagation();
        next();
    };

    viewer.appendChild(close);
    viewer.appendChild(counter);
    viewer.appendChild(left);
    viewer.appendChild(img);
    viewer.appendChild(right);

document.body.appendChild(viewer);

setTimeout(()=>{
    viewer.classList.add("show");
},20);

document.body.style.overflow = "hidden";

updateImage();
}


function updateImage(){

    const img = viewer.querySelector(".viewer-img");
    const counter = viewer.querySelector(".viewer-counter");

    if(!img || !counter) return;

    img.style.opacity = 0;

    setTimeout(() => {

        img.src = images[current].dataset.full;
        img.style.opacity = 1;

        counter.textContent = (current + 1) + " / " + images.length;

        // preload next image
        let nextIndex = (current + 1) % images.length;
        let prevIndex = (current - 1 + images.length) % images.length;

        const preloadNext = new Image();
        preloadNext.src = images[nextIndex].dataset.full;

        const preloadPrev = new Image();
        preloadPrev.src = images[prevIndex].dataset.full;

    }, 120);
}

    function closeViewer(){
        if(viewer){
            viewer.remove();
            viewer = null;
            document.body.style.overflow = "";
        }
    }

    function next(){
        current = (current + 1) % images.length;
        viewer.querySelector("img").src = images[current].dataset.full;
        updateImage();
    }

    function prev(){
        current = (current - 1 + images.length) % images.length;
        viewer.querySelector("img").src = images[current].dataset.full;
        updateImage();
    }

    images.forEach((img,i)=>{
        img.addEventListener("click", ()=>openViewer(i));
    });

    document.addEventListener("keydown", e => {

        if(!viewer) return;

        if(e.key === "Escape") closeViewer();
        if(e.key === "ArrowRight") next();
        if(e.key === "ArrowLeft") prev();

    });

    document.addEventListener("click", e => {
        if(e.target.classList.contains("image-viewer")){
            closeViewer();
        }
    });

});

new Swiper(".screenshot-swiper", {
    slidesPerView: 1.2,
    spaceBetween: 15,
    speed: 600,

    preventClicks:false,
    preventClicksPropagation:false,

    pagination:{
        el: ".swiper-pagination",
        clickable:true
    },

    navigation:{
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
    },

    breakpoints:{
        476:{slidesPerView:1},
        576:{slidesPerView:2},
        768:{slidesPerView:3},
        992:{slidesPerView:4},
        1200:{slidesPerView:5}
    }
});
</script>
