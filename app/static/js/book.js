"use strict";

// For all collapse buttons (author/genre)
const button = document.getElementsByClassName('button');
for (let i = 0; i < button.length; i++) {
    button[i].addEventListener('click', (e)=>{
        e.target.nextElementSibling.classList.toggle('collapse_id')});
}



// Draft Class
// class Paginator{
//     constructor(page_number, amount, maximum, replace_id){
//         this.page_number = page_number;
//         this.amount = amount;
//         this.maximum = maximum;
//         this.scheduled_function = false;
//         this.delay_by_in_ms = 700;
//         this.table_div = document.querySelector(`#${replace_id}`);
//         this.paginator = this.create();
//     }
//
//     create(){
//         let part = `<ul class="pagination justify-content-center">
//     <li class="page-item ${ (this.page_number<2) ? 'disabled': ''} ">
//     <a class="page-link" href="/?page=${ this.page_number>1 ? (this.page_number-1): 1 }" tabindex="-1">Previous</a> </li>`;
//
//         this.amount.forEach(element => part += `<li class="page-item"><a class="page-link" href="/?page=${ element }">${ element }</a></li>`);
//
//         part += `<li class="page-item ${ (this.maximum - this.page_number>0) ? '' : 'disabled' } ">
//     <a class="page-link" href="/?page=${ (this.maximum - this.page_number>0) ? (this.page_number + 1): this.maximum}">Next</a>
//     </li>
//     </ul>`;
//         return part;
//     }
//
//     fetch_call(endpoint, request_parameters){
//         fetch(endpoint, {
//             body: JSON.stringify(request_parameters),
//             headers: {
//                 'Content-Type': 'application/json'
//             },
//             method: 'POST',
//             credentials: 'include',
//             // mode:"cors"
//         })
//             .then(response => response.json()).then(data=> {
//             table_div.innerHTML = data["html_from_view"];
//         })
//             .catch(function (error) {
//                 console.log('Request failed', error);
//             })
//     };
//
//     make_request(){
//         const request_parameters = {
//             flag: 'XmlHttpRequest',
//         };
//
//         if (this.scheduled_function) {
//             clearTimeout(this.scheduled_function)
//         }
//         this.table_div.classList.add("blink");
//         this.scheduled_function = setTimeout(this.fetch_call, this.delay_by_in_ms, endpoint, request_parameters);
//         this.table_div.classList.remove("blink");
//     }
//
//     render(){
//
//         for (let i = 0; i < button.length; i++) {
//             button.forEach(element=>element.addEventListener('click', this.make_request));
//         }
//     }
//
// }
