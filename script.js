document.addEventListener('DOMContentLoaded', () => {
    const wheelInner = document.querySelector('.wheel-inner');
    const spinBtn = document.getElementById('spin-btn');
    const result = document.getElementById('result');

    const prizes = [
        { text: '10% OFF', color: '#FF6B6B' },
        { text: '5% OFF', color: '#4ECDC4' },
        { text: 'Ingyenes szállítás', color: '#45B7D1' },
        { text: '15% OFF', color: '#96CEB4' },
        { text: '20% OFF', color: '#FFEEAD' },
        { text: 'Próbáld újra', color: '#D4A5A5' }
    ];

    // Create segments
    prizes.forEach((prize, i) => {
        const segment = document.createElement('div');
        segment.className = 'segment';
        segment.id = `segment-${i}`;
        segment.style.backgroundColor = prize.color;
        
        const angle = (360 / prizes.length) * i;
        segment.style.transform = `rotate(${angle}deg)`;

        const text = document.createElement('span');
        text.textContent = prize.text;
        segment.appendChild(text);

        wheelInner.appendChild(segment);
    });

    let spinning = false;
    let currentRotation = 0;

    spinBtn.addEventListener('click', () => {
        if (spinning) return;
        
        spinning = true;
        spinBtn.disabled = true;
        result.textContent = '';

        const spins = 5 + Math.floor(Math.random() * 5);
        const extra = Math.floor(Math.random() * 360);
        const totalRotation = spins * 360 + extra;
        currentRotation += totalRotation;

        wheelInner.style.transform = `rotate(${currentRotation}deg)`;

        setTimeout(() => {
            spinning = false;
            spinBtn.disabled = false;

            const winningIndex = Math.floor(
                (360 - (currentRotation % 360)) / (360 / prizes.length)
            );
            result.textContent = `Nyereményed: ${prizes[winningIndex].text}!`;
        }, 4000);
    });
});