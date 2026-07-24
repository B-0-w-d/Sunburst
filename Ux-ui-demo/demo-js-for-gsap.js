document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(".scroll-container");
    const panels = document.querySelectorAll(".panel");

    let currentIndex = 0;
    let locked = false;

    function scrollToSection(index) {
        locked = true;

        container.scrollTo({
            left: index * window.innerWidth,
            behavior: "smooth",
        });

        setTimeout(() => {
            locked = false;
        }, 1000);
    }

    window.addEventListener(
        "wheel",
        (e) => {
            e.preventDefault();

            if (locked) return;

            if (Math.abs(e.deltaY) < 10) return;

            if (e.deltaY > 0) {
                if (currentIndex < panels.length - 1) {
                    currentIndex++;
                }
            } else {
                if (currentIndex > 0) {
                    currentIndex--;
                }
            }

            scrollToSection(currentIndex);
        },
        { passive: false }
    );
});    