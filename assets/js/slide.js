document.addEventListener("DOMContentLoaded", function(){

    // klik w kafel
    document.querySelectorAll(".weselny-tile a").forEach(link => {

        link.addEventListener("click", function(e){

            e.preventDefault();

            const container = document.querySelector(".weselny-slide");

            if(container){
                container.classList.add("weselny-slide-out");

                setTimeout(() => {
                    window.location.href = link.href;
                }, 200);
            } else {
                window.location.href = link.href;
            }

        });

    });

    // powrót
    document.addEventListener("click", function(e){

        if(e.target.closest(".weselny-back")){

            e.preventDefault();

            const link = e.target.closest("a");
            const container = document.querySelector(".weselny-slide");

            if(container){
                container.style.animation = "slideOutBack 0.25s ease forwards";

                setTimeout(()=>{
                    window.location.href = link.href;
                },200);
            }
        }

    });

});