/* SPDX-License-Identifier: GPL-3.0-or-later */
(function(root){'use strict';
 const stop=new Set('the a an and or of to in on for with from by is are has have as at this that after new says said ve veya ile bir bu şu da de için gibi göre olarak daha son yeni oldu var olan istendi die der das und ein eine mit von für est les des une dans pour'.split(' '));
 function normalized(text){return String(text).normalize('NFKD').replace(/\p{M}/gu,'').toLocaleLowerCase('tr').replace(/ı/g,'i').replace(/[^\p{L}\p{N}\s]/gu,' ').trim().replace(/\s+/g,' ');}
 function tokens(text){return [...new Set(normalized(text).split(' ').filter(w=>w.length>2&&!stop.has(w)))];}
 function similar(a,b,threshold=0.62){
  if(a.url&&b.url&&a.url===b.url)return true;
  if(a.date&&b.date&&Math.abs(a.date-b.date)>36*3600*1000)return false;
  const na=normalized(a.title),nb=normalized(b.title);if(na===nb&&na.length>8)return true;
  const x=tokens(a.title),y=tokens(b.title);if(x.length<3||y.length<3)return false;
  const nums=s=>[...s.matchAll(/\b\d+(?:[.,]\d+)?\b/g)].map(m=>m[0]);const xn=nums(na),yn=nums(nb);
  if(xn.length&&yn.length&&!xn.some(n=>yn.includes(n)))return false;
  const common=x.filter(t=>y.includes(t)).length;if(common<3)return false;
  const dice=2*common/(x.length+y.length),contain=common/Math.min(x.length,y.length);
  return dice>=threshold||(common>=4&&contain>=0.9&&dice>=threshold-0.15);
 }
 function videoId(url){try{const u=new URL(url);let id='';if(u.hostname==='youtu.be')id=u.pathname.slice(1);else if(/^(www\.|m\.)?youtube(?:-nocookie)?\.com$/.test(u.hostname))id=u.searchParams.get('v')||u.pathname.match(/^\/(?:embed|shorts)\/([^/]+)/)?.[1]||'';return /^[\w-]{11}$/.test(id)?id:null;}catch{return null;}}
 function blocked(title,tags,deny,interest){const text=normalized(title+' '+tags.join(' ')),has=words=>words.some(w=>{const term=normalized(w);return term&&(' '+text+' ').includes(' '+term+' ');});return !has(interest)&&has(deny);}
 const api={normalized,tokens,similar,videoId,blocked};root.ContentStudioCore=api;if(typeof module!=='undefined')module.exports=api;
})(typeof window!=='undefined'?window:globalThis);
