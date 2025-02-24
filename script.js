// Mobile menu toggle
document.querySelector('.mobile-menu-button').addEventListener('click', function() {
    document.querySelector('.mobile-menu').classList.toggle('hidden');
});

// Fortune wheel functionality
function wheelOfFortune(selector) {
    const node = document.querySelector(selector);
    if (!node) return;
    
    const spin = document.getElementById('spinButton');
    const wheel = node.querySelector('ul');
    let animation;
    let previousEndDegree = 0;
    
    if (spin) {
        spin.addEventListener('click', () => {
            if (spin.disabled) return;
            
            if (animation) {
                animation.cancel(); // Reset the animation if it already exists
            }
            
            const randomAdditionalDegrees = Math.random() * 360 + 1800;
            const newEndDegree = previousEndDegree + randomAdditionalDegrees;
            
            animation = wheel.animate([
                { transform: `rotate(${previousEndDegree}deg)` },
                { transform: `rotate(${newEndDegree}deg)` }
            ], {
                duration: 4000,
                direction: 'normal',
                easing: 'cubic-bezier(0.440, -0.205, 0.000, 1.130)',
                fill: 'forwards',
                iterations: 1
            });
            
            previousEndDegree = newEndDegree;
        });
    }
}

// Show modal when page loads
window.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('fortuneModal');
    modal.style.display = 'block';
    
    // Initialize wheel of fortune
    wheelOfFortune('.ui-wheel-of-fortune');
    
    // Close modal functionality
    document.getElementById('closeModal').addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    // Also close when clicking outside the modal content
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});