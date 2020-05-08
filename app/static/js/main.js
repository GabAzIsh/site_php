const user_input = document.querySelector("#search-icon");
const table_div = document.querySelector('#replaceable-content');
const endpoint = '/';
const delay_by_in_ms = 700;
let scheduled_function = false;
const button = document.getElementsByClassName('button');

function setCookie(cname, cvalue, exmin) {
	let d = new Date();
	d.setTime(d.getTime() + (exmin*60*1000));
	let expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function delete_cookie( name ) {
	document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 UTC;';
}


// For JQuery
let ajax_call = function (endpoint, request_parameters) {
	$.ajax({
		dataType: "json",
		method: 'post',
		url: endpoint,
		data: request_parameters,
		xhrFields: {
			withCredentials: true
		}
	})
		.done(response => {
			// fade out the table_div, then:
			$('#replaceable-content').fadeTo('slow', 0).promise().then(() => {
				// replace the HTML contents
				$('#replaceable-content').html(response['html_from_view']);
				// fade-in the div with new contents
				$('#replaceable-content').fadeTo('slow', 1)
			})
		})
};



// For Fetch API
let fetch_call = function(endpoint, request_parameters){
	fetch(endpoint, {
		body: JSON.stringify(request_parameters),
		headers: {
			'Content-Type': 'application/json'
		},
		method: 'POST',
		credentials: 'include',
		// mode:"cors"
	})
		.then(response => response.json()).then(data=> {
		table_div.innerHTML = data["html_from_view"];

		let string = '';
		document.getElementById("paginator").innerHTML = paginator(data["page_number"]) (Object.values(data["amount"])) (data["maximum"]) (string);
		setTimeout(()=>table_div.classList.remove("blink"), 700);
	})
		.catch(function (error) {
			console.log('Request failed', error);
		})
};


// Add Event listener for Input Element of the form for unfocused case
let input = user_input.addEventListener('blur', input_blur);


// For paginator update
let paginator = page_number=>amount=>maximum=>string =>
{ string += `<ul class="pagination justify-content-center">
                                     <li class="page-item" title="page" value="${ (page_number>1) ? (page_number-1) + `" style="color: #007bff; cursor: pointer;" onclick="get_by(this)" `: `"` } >
                                        <a class="page-link"  >Previous</a> </li>`;
	amount.forEach(element => string += `<li class="page-item" onclick="get_by(this)" style="color: #007bff; cursor: pointer;" title="page" value="${ element }" ><a  class="page-link" >${ element }</a></li>`);
	string +=     `<li class="page-item" title="page" value="${ (maximum - page_number>0) ? (page_number + 1) + '" style="color: #007bff; cursor: pointer;" onclick="get_by(this)" ' : '"' } >
                                        <a class="page-link"  >Next</a>
                                     </li>
                                </ul>`;
	return string;};

// For old style AJAX request (JQuery)
function input_blur(e) {
	// SET paginator hidden
	document.getElementById("paginator").hidden = true;

	// SET or REMOVE to Cookies
	if(e.target.value){
		setCookie(e.target.name, e.target.value, 10);
	} else {
		delete_cookie( e.target.name );
	}

	// SET flag to request body
	const request_parameters = {
		flag: 'XmlHttpRequest',
		};

	// if scheduled_function is NOT false, cancel the execution of the function
	if (scheduled_function) {
		clearTimeout(scheduled_function)
	}

	// setTimeout returns the ID of the function to be executed
	scheduled_function = setTimeout(ajax_call, delay_by_in_ms, endpoint, request_parameters)

	// If cookies is empty unhidden paginator
	if(!document.cookie){
		document.getElementById("paginator").hidden = false;
	}
}


// For new style AJAX request (Fetch API)
function get_by(e){


	// SET Cookie for page
	if (e.title === 'page') {
		setCookie(e.title, e.value, 0.05);
	} else {
		delete_cookie( e.name );
	}


	// SET Cookie for checkbox
	if (e.checked ) {
		setCookie(e.name, e.value, 10);
	} else {
		delete_cookie( e.name );
	}

	const request_parameters = {
		flag: 'XmlHttpRequest',
	};
	// request_parameters[e.name] =  [e.value]; // value of user_input: the HTML element with ID user-input

	if (scheduled_function) {
		clearTimeout(scheduled_function)
	}

	// Add blink class for the table
	table_div.classList.add("blink");

	// Execute AJAX with timeout
	scheduled_function = setTimeout(fetch_call, delay_by_in_ms, endpoint, request_parameters)

	// Remove  blink class for the table
	// setTimeout(table_div.classList.remove("blink"), 2000);

	// If cookies is empty unhidden paginator
	// if(!document.cookie){
	// 	document.getElementById("paginator").hidden = false;
	// }

}

// Function for collapse author/genre
for (let i = 0; i < button.length; i++) {
	button[i].addEventListener('click', (e)=>{
		e.target.nextElementSibling.classList.toggle('collapse_id')});
}