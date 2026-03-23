document.addEventListener("DOMContentLoaded", function(){

    if(typeof particlesJS === "undefined") return;

    particlesJS("weselny-particles", {
        particles: {
            number: {
                value: 80,
                density: {
                    enable: true,
                    value_area: 800
                }
            },
            color: {
                value: "#ffffff"
            },
            shape: {
                type: "image",
                image: {
                    src: "/wp-content/uploads/2026/03/heart_259424.png",
                    width: 100,
                    height: 100
                }
            },
            opacity: {
                value: 0.3
            },
            size: {
                value: 8,
                random: true
            },

            /* 🔥 TO JEST KLUCZ */
            line_linked: {
                enable: false
            },

            move: {
                enable: true,
                speed: 0.5,
                out_mode: "out"
            }
        },

        interactivity: {
            detect_on: "canvas",
            events: {
                onhover: {
                    enable: false
                },
                onclick: {
                    enable: true,
                    mode: "push"
                },
                resize: true
            },
            modes: {
                push: {
                    particles_nb: 4
                }
            }
        },

        retina_detect: true
    });

});