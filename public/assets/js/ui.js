;(function(w,$){
  var ui={};
  ui.loadingShow=function(sel){var el=typeof sel==='string'?document.querySelector(sel):sel;if(!el)return;var o=document.createElement('div');o.className='loading-overlay';o.innerHTML='<div class="spinner-border text-primary"></div><div class="small text-muted">Memuat...</div>';o.style.position='absolute';o.style.inset='0';o.style.display='grid';o.style.placeItems='center';el.style.position='relative';el.appendChild(o)};
  ui.loadingHide=function(sel){var el=typeof sel==='string'?document.querySelector(sel):sel;if(!el)return;var o=el.querySelector('.loading-overlay');if(o)o.remove()};
  ui.emptyRow=function(colspan,text){var tr=document.createElement('tr');var td=document.createElement('td');td.colSpan=colspan;td.className='text-center text-muted';td.textContent=text||'Tidak ada data';tr.appendChild(td);return tr};
  ui.tableSkeleton=function(tbody,cols,rows){if(!tbody)return;tbody.innerHTML='';for(var r=0;r<(rows||5);r++){var tr=document.createElement('tr');for(var c=0;c<(cols||3);c++){var td=document.createElement('td');td.innerHTML='<span class=\"placeholder col-8\"></span>';td.className='placeholder-glow';tr.appendChild(td)}tbody.appendChild(tr)}};
  ui.clearTable=function(tbody){if(!tbody)return;tbody.innerHTML=''};
  w.UI=ui;
})(window,window.jQuery); 
