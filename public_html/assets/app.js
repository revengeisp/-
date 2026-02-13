function openModal(id){ document.getElementById(id).style.display='flex'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }

document.addEventListener('click', (e)=>{
  if(e.target.classList.contains('modal-backdrop')){
    e.target.style.display='none';
    return;
  }

  const card = e.target.closest('[data-book-modal="1"]');
  if(card){
    const isIssued = card.dataset.issued === '1';
    document.getElementById('mBookTitle').textContent  = card.dataset.title || '';
    document.getElementById('mBookAuthor').textContent = card.dataset.author || '';
    document.getElementById('mBookGenre').textContent  = card.dataset.genre || '';
    document.getElementById('mBookIsbn').textContent   = card.dataset.isbn || '';
    document.getElementById('mBookStatus').textContent = isIssued ? 'Выдана' : 'Доступна';

    const btn = document.getElementById('mBookBtn');
    const hidden = document.getElementById('mBookId');
    hidden.value = card.dataset.id || '0';

    if(isIssued){
      btn.disabled = true;
      btn.textContent = 'Недоступна';
      btn.classList.add('secondary');
    }else{
      btn.disabled = false;
      btn.textContent = 'Взять книгу';
      btn.classList.remove('secondary');
    }

    openModal('mBook');
  }
});
