import gsap from "gsap";

window.addEventListener("load", () => {

    const tl = gsap.timeline();

    tl.from(".nav", {
        y: -100,
        opacity: 0,
        duration: 1
    })

    .from(".home__tour", {
        y: -30,
        opacity: 0,
        duration: 0.6
    })

    .from(".home__title-line", {
        y: 150,
        opacity: 0,
        stagger: 0.2,
        duration: 1
    })

    .from(".home__description", {
        y: 50,
        opacity: 0,
        duration: 0.8
    })

    .from(".home__buttons", {
        y: 50,
        opacity: 0,
        duration: 0.8
    })

    .from(".home__status", {
        x: 100,
        opacity: 0,
        duration: 0.8
    })

    .from(".home__scroll", {
        opacity: 0,
        duration: 0.8
    });

});