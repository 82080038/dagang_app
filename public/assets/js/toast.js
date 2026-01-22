;(function(){
  function ensureContainer(){
    var c = document.getElementById('globalToastContainer');
    if (!c){
      c = document.createElement('div');
      c.id = 'globalToastContainer';
      c.className = 'toast-container position-fixed top-0 end-0 p-3';
      document.body.appendChild(c);
    }
    return c;
  }
  function cls(t){
    if (t === 'success') return 'bg-success text-white';
    if (t === 'error' || t === 'danger') return 'bg-danger text-white';
    if (t === 'warning') return 'bg-warning';
    return 'bg-primary text-white';
  }
  function posClass(p){if(p==='bottom-end')return'toast-container position-fixed bottom-0 end-0 p-3';if(p==='bottom-start')return'toast-container position-fixed bottom-0 start-0 p-3';if(p==='top-start')return'toast-container position-fixed top-0 start-0 p-3';return'toast-container position-fixed top-0 end-0 p-3'}
  window.ToastConfig=window.ToastConfig||{position:'top-end',delays:{success:2000,error:4000,warning:3000,primary:3000}};
  var lastMsg=''; var lastAt=0; var minGap=600;
  window.showToast = function(type, message, options){
    var now=Date.now();
    if (message===lastMsg && now-lastAt<minGap) return;
    lastMsg=message; lastAt=now;
    var container = ensureContainer();
    container.className=posClass(window.ToastConfig.position||'top-end');
    var el = document.createElement('div');
    el.className = 'toast align-items-center';
    el.setAttribute('role','alert');
    el.setAttribute('aria-live','assertive');
    el.setAttribute('aria-atomic','true');
    el.innerHTML = '<div class="d-flex '+cls(type)+'"><div class="toast-body">'+(message||'')+'</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    container.appendChild(el);
    var d=(window.ToastConfig.delays||{})[type]||3000;
    var t = new bootstrap.Toast(el, Object.assign({delay:d, autohide:true}, options||{}));
    t.show();
    var list=container.querySelectorAll('.toast'); if (list.length>5){container.removeChild(list[0])}
  };
  window.showNotification = function(message, type){
    window.showToast(type||'primary', message||'');
  };
})(); 
