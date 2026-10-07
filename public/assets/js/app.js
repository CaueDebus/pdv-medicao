/*
 * Melhorias progressivas: a aplicação funciona inteira sem este arquivo.
 * Os formulários continuam sendo formulários; aqui só evitamos o recarregar
 * quando ele não é necessário.
 *
 * A escuta é delegada no documento porque as regiões da página são
 * substituídas em tempo de execução — handler preso ao elemento se perderia
 * na primeira troca.
 */

document.addEventListener('click', (event) => {
  const link = event.target.closest('.nav-link');

  if (!link) {
    return;
  }

  document.querySelectorAll('.nav-link.is-active').forEach((active) => active.classList.remove('is-active'));

  if (!link.classList.contains('is-locked')) {
    link.classList.add('is-active');
  }
});

/**
 * Substitui o conteúdo de uma região pela versão que o servidor devolveu.
 */
const swapRegion = (nextDocument, selector) => {
  const next = nextDocument.querySelector(selector);
  const current = document.querySelector(selector);

  if (next && current) {
    current.innerHTML = next.innerHTML;
  }
};

let toastTimer = null;

const showToast = (message, tone) => {
  const toast = document.getElementById('toast');
  const text = toast?.querySelector('.toast-text');

  if (!toast || !text) {
    return;
  }

  text.textContent = message;
  toast.classList.remove('tone-ok', 'tone-info', 'tone-warn', 'tone-danger');
  toast.classList.add(`tone-${tone}`, 'is-visible');

  window.clearTimeout(toastTimer);
  // Mensagem de erro fica mais tempo: ela costuma pedir uma ação de quem lê.
  toastTimer = window.setTimeout(
    () => toast.classList.remove('is-visible'),
    tone === 'danger' || tone === 'warn' ? 4200 : 2600,
  );
};

/*
 * O servidor sempre renderiza a confirmação como aviso no topo da tela, que é
 * o que vale sem JavaScript. Havendo JavaScript, ela sobe como toast e sai da
 * tela — a mensagem continua tendo uma origem só, o PHP.
 */
const promoteFlashToToast = (root = document) => {
  const flash = root.querySelector('.alert[data-flash]');

  if (!flash) {
    return;
  }

  const message = flash.textContent.trim();
  const tone = flash.dataset.flash || 'info';

  flash.remove();

  if (message !== '') {
    showToast(message, tone);
  }
};

promoteFlashToToast();

/*
 * Liga/desliga de ponto de variação sem recarregar a tela.
 *
 * O POST é o mesmo do formulário e o servidor responde com o redirect de
 * sempre, que o fetch segue — então a resposta já é a tela atualizada. Em vez
 * de recriar o estado no navegador, trocamos as regiões que mudaram pelo HTML
 * que o PHP acabou de renderizar: a regra de variabilidade continua vivendo
 * em um lugar só.
 */
document.addEventListener('submit', async (event) => {
  const form = event.target.closest('form[data-inline-toggle]');

  if (!form) {
    return;
  }

  event.preventDefault();

  const button = form.querySelector('button');
  button?.classList.add('is-busy');
  if (button) {
    button.disabled = true;
  }

  let html = null;

  try {
    const response = await fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      headers: { 'X-Requested-With': 'fetch' },
      credentials: 'same-origin',
    });

    if (response.ok) {
      html = await response.text();
    }
  } catch (error) {
    html = null;
  }

  // Falha de rede ou resposta inesperada: deixa o formulário seguir o
  // caminho normal, com recarregamento, em vez de engolir a ação.
  if (html === null) {
    form.submit();
    return;
  }

  const nextDocument = new DOMParser().parseFromString(html, 'text/html');

  swapRegion(nextDocument, '.nav-list');
  swapRegion(nextDocument, '.content-area');

  // A confirmação veio dentro do HTML trocado: tira do topo e sobe como toast.
  promoteFlashToToast();
});
