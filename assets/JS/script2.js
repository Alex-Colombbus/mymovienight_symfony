///// Barre Glissante /////

const barreMin = document.querySelector('#barreMin');
const barreMax = document.querySelector('#barreMax');
const chiffreMin = document.querySelector('#chiffreMin');
const chiffreMax = document.querySelector('#chiffreMax');

// Fonction pour gérer le slider de gauche
function controlechiffreMin(barreMin, chiffreMin, chiffreMax, controleSlider) {
    const [from, to] = getParsed(chiffreMin, chiffreMax);
    remplissageBarre(chiffreMin, chiffreMax, '#C6C6C6', '#25daa5', controleSlider);
    if (from > to) {
        barreMin.value = to;
        chiffreMin.value = to;
    } else {
        barreMin.value = from;
    }
}
    
function controlechiffreMax(barreMax, chiffreMin, chiffreMax, controleSlider) {
    const [from, to] = getParsed(chiffreMin, chiffreMax);
    remplissageBarre(chiffreMin, chiffreMax, '#C6C6C6', '#25daa5', controleSlider);
    setToggleAccessible(chiffreMax);
    if (from <= to) {
        barreMax.value = to;
        chiffreMax.value = to;
    } else {
        chiffreMax.value = from;
    }
}

function controlebarreMin(barreMin, barreMax, chiffreMin) {
  const [from, to] = getParsed(barreMin, barreMax);
  remplissageBarre(barreMin, barreMax, '#C6C6C6', '#25daa5', barreMax);
  if (from > to) {
    barreMin.value = to;
    chiffreMin.value = to;
  } else {
    chiffreMin.value = from;
  }
}

function controlebarreMax(barreMin, barreMax, chiffreMax) {
  const [from, to] = getParsed(barreMin, barreMax);
  remplissageBarre(barreMin, barreMax, '#C6C6C6', '#25daa5', barreMax);
  setToggleAccessible(barreMax);
  if (from <= to) {
    barreMax.value = to;
    chiffreMax.value = to;
  } else {
    chiffreMax.value = from;
    barreMax.value = from;
  }
}

function getParsed(currentFrom, currentTo) {
  const from = parseInt(currentFrom.value, 10);
  const to = parseInt(currentTo.value, 10);
  return [from, to];
}

function remplissageBarre(from, to, sliderColor, rangeColor, controleSlider) {
    const rangeDistance = to.max-to.min;
    const fromPosition = from.value - to.min;
    const toPosition = to.value - to.min;
    controleSlider.style.background = `linear-gradient(
      to right,
      ${sliderColor} 0%,
      ${sliderColor} ${(fromPosition)/(rangeDistance)*100}%,
      ${rangeColor} ${((fromPosition)/(rangeDistance))*100}%,
      ${rangeColor} ${(toPosition)/(rangeDistance)*100}%, 
      ${sliderColor} ${(toPosition)/(rangeDistance)*100}%, 
      ${sliderColor} 100%)`;
}

function setToggleAccessible(currentTarget) {
  const barreMax = document.querySelector('#barreMax');
  if (Number(currentTarget.value) <= 0 ) {
    barreMax.style.zIndex = 2;
  } else {
    barreMax.style.zIndex = 0;
  }
}

remplissageBarre(barreMin, barreMax, '#C6C6C6', '#25daa5', barreMax);
setToggleAccessible(barreMax);

barreMin.addEventListener('change',() => controlebarreMin(barreMin, barreMax, chiffreMin));
barreMax.oninput = () => controlebarreMax(barreMin, barreMax, chiffreMax);
chiffreMin.oninput = () => controlechiffreMin(barreMin, chiffreMin, chiffreMax, barreMax);
chiffreMax.oninput = () => controlechiffreMax(barreMax, chiffreMin, chiffreMax, barreMax);