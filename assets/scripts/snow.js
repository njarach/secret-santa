  function createSnowEffect() {
  const container = document.getElementById('snow-container');
  const snowflakeSymbols = ['❄', '❅', '❆', '✻', '✼', '❋'];

  function createSnowflake() {
  const snowflake = document.createElement('div');
  snowflake.className = 'snowflake';
  snowflake.innerHTML = snowflakeSymbols[Math.floor(Math.random() * snowflakeSymbols.length)];

  snowflake.style.left = Math.random() * 100 + '%';

  const size = Math.random() * 0.8 + 0.8;
  snowflake.style.fontSize = size + 'rem';

  const duration = Math.random() * 8 + 6;
  snowflake.style.animationDuration = duration + 's';

  const delay = Math.random() * 2;
  snowflake.style.animationDelay = delay + 's';

  container.appendChild(snowflake);

  setTimeout(() => {
  if (snowflake.parentNode) {
  snowflake.parentNode.removeChild(snowflake);
}
}, (duration + delay) * 1000);
}

  setInterval(createSnowflake, 300);

  for (let i = 0; i < 10; i++) {
  setTimeout(createSnowflake, i * 200);
}
}

  document.addEventListener('DOMContentLoaded', createSnowEffect);
