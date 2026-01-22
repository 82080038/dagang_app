;(function(w,$){
  function jsonHeaders(opts){var h=opts&&opts.headers||{};h['X-Requested-With']='XMLHttpRequest';return h}
  function fetchJSON(url,opts){opts=opts||{};opts.headers=jsonHeaders(opts);return fetch(url,opts).then(function(r){var ct=r.headers.get('content-type')||'';if(ct.indexOf('application/json')===-1){return r.text().then(function(t){throw new Error('Non-JSON: '+t.slice(0,120))})}return r.json()}).catch(function(err){showToast('error',(opts.errorMessage||'Terjadi kesalahan')+': '+(err.message||err));throw err})}
  function ajaxJSON(options){return new Promise(function(res,rej){var o=$.extend({dataType:'json',headers:{'X-Requested-With':'XMLHttpRequest'}},options||{});o.success=function(r){res(r)};o.error=function(x,s,e){showToast('error',(o.errorMessage||'Terjadi kesalahan sistem'));rej(e||s)};$.ajax(o)})}
  w.http={fetchJSON:fetchJSON,ajaxJSON:ajaxJSON};
})(window,window.jQuery); 
