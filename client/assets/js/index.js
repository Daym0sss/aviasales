function checkBookTicketsForm(event)
{
    event.preventDefault();
    var passangersCount = (event.currentTarget.elements.length - 5) / 6;
    var passports = [];
    for(var i = 1;i <= passangersCount;i++)
    {
        if(!(passports.includes(document.getElementById("passport_num" + i).value.trim())))
        {
            passports.push(document.getElementById("passport_num" + i).value.trim());
        }
    }

    if (passports.length == passangersCount)
    {
        $.ajax({
            type: "POST",
            url: "http://localhost/kursach/server/user/registerForFlight",
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            dataType : "json",
            data: $('form').serialize(),
            success: function (data)
            {
                document.getElementById("alertMessageSuccess").style.display = "block";
            }
        });
    }
    else
    {
        document.getElementById("alertMessageFail").style.display = "block";
    }
}

function checkIfAuthorized(event, user_id)
{
    event.preventDefault();
    var form = event.currentTarget;
    $.ajax({
        type: "POST",
        url: "http://localhost/kursach/server/user/checkIfAuthorized",
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        dataType : "json",
        data: {},
        success: function (data)
        {
            if (data.data.user_id == null)
            {
                document.getElementById("alert-text-fail").innerText = "You are not authorized to book tickets";
                document.getElementById("alertMessageFail").style.display = "block";
                document.getElementById("whereToRedirect").innerText = "login";
            }
            else if (data.data.role == 1)
            {
                document.getElementById("alert-text-fail").innerText = "Your role is admin";
                document.getElementById("alertMessageFail").style.display = "block";
                document.getElementById("whereToRedirect").innerText = "index";
            }
            else
            {
                form.submit();
            }
        }
    });
}

function agreeNotAuthorized()
{
    if (document.getElementById("whereToRedirect").innerText == "index")
    {
        window.location.href = "http://localhost/kursach/server/";
    }
    else
    {
        window.location.href = "http://localhost/kursach/server/login";
    }
}

function checkLogout()
{
    document.getElementById("confirmLogout").style.display = "block";
}

function logoutUser()
{
    document.getElementById("confirmLogout").style.display = "none";
    document.getElementById("logoutForm").submit();
}

function loginUser(event)
{
    event.preventDefault();
    var login = event.currentTarget.elements[0].value;
    var password = event.currentTarget.elements[1].value;
    $.ajax({
        type: "POST",
        url: "http://localhost/kursach/server/user/login",
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        dataType : "json",
        data: {login: login, password: password},
        success: function (data)
        {
            if (data.message == "Wrong credentials")
            {
                document.getElementById('alertMessageFail').style.display = "block";
            }
            else
            {
                document.getElementById('alertMessageSuccess').style.display = "block";
            }

        }
    });
}

function loginSuccessCloseClick()
{
    document.getElementById("alertMessageSuccess").style.display = "none";
    window.location.href = "http://localhost/kursach/server/";
}

function loginFailCloseClick()
{
    document.getElementById("alertMessageFail").style.display = "none";
}

function registerUser(event)
{
    event.preventDefault();
    var firstName = event.currentTarget.elements[0].value;
    var secondName = event.currentTarget.elements[1].value;
    var lastName = event.currentTarget.elements[2].value;
    var email = event.currentTarget.elements[3].value;
    var phone = event.currentTarget.elements[4].value;
    var login = event.currentTarget.elements[5].value;
    var password = event.currentTarget.elements[6].value;

    if (login.length < 5)
    {
        document.getElementById("alert-text-fail").innerText = "Login length must be at least 5 symbols";
        document.getElementById('alertMessageFail').style.display = "block";
    }
    else if (password.length < 8)
    {
        document.getElementById("alert-text-fail").innerText = "Password length must be at least 8 symbols";
        document.getElementById('alertMessageFail').style.display = "block";
    }
    else
    {
        $.ajax({
            type: "POST",
            url: "http://localhost/kursach/server/user/register",
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            dataType : "json",
            data: {login: login, password: password, firstName: firstName, secondName: secondName, lastName: lastName, email: email, phone: phone, login: login, password: password},
            success: function (data)
            {
                if (data.message == "User with this login already exists")
                {
                    document.getElementById('alert-text-fail').innerText = "User with this login already exists";
                    document.getElementById('alertMessageFail').style.display = "block";
                }
                else
                {
                    document.getElementById('alertMessageSuccess').style.display = "block";
                }
            }
        });
    }
}

function loadAllFlights()
{
    $.ajax({
        type: "POST",
        url: "http://localhost/kursach/server/user/getBookedFlights",
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        dataType : "json",
        data: {period: "all"},
        success: function (data)
        {
            var flights = data.flights;
            var keys = Object.keys(flights);
            if (flights.length == 0)
            {
                document.getElementById("flightsInformation").innerHTML = "<h2>No flights have been found for this period</h2>";
                document.getElementById("marginForFooter").style.display = "block";
            }
            else
            {
                document.getElementById("marginForFooter").style.display = "none";
                drawBookedFlights(flights, keys);
            }
        }
    });
}

function drawBookedFlights(flights, keys)
{
    var i = 0;
    var body = "";
    body += "<table class='table table-hover table-dark table-gradient'><tr><th>#</th><th style='width: 300px'>Departure</th><th style='width: 300px'>Arrival</th><th style='width: 700px'>Passangers</th><th style='width: 170px'>Flight class : Price</th><th>Actions</th></tr>";
    for(i = 0;i < keys.length;i++)
    {
        var flight = flights[keys[i]];
        var counter = i + 1;

        body += "<tr>";
        body += "<td>" + counter + "</td>";

        body += "<td>" + flight.trip_information.departure_country + "/" + flight.trip_information.departure_city + "/" + flight.trip_information.departure_airport + "/Terminal " + flight.trip_information.departure_terminal + " Date: " + flight.trip_information.departure_date +  " Time: " + flight.trip_information.departure_time + "</td>";
        body += "<td>" + flight.trip_information.arrival_country + "/" + flight.trip_information.arrival_city + "/" + flight.trip_information.arrival_airport + "/Terminal " + flight.trip_information.arrival_terminal + " Date: " + flight.trip_information.arrival_date +  " Time: " + flight.trip_information.arrival_time + "</td>";

        var passangers = flight.passangers_information;
        var result_price = 0;
        var price_default = 0;
        var price_child = 0;
        var price_baby = 0;

        if (passangers[0].class == "econom")
        {
            price_default = parseInt(flight.trip_information.econom_place_price_default);
            price_child = parseInt(flight.trip_information.econom_place_price_child);
            price_baby = parseInt(flight.trip_information.econom_place_price_baby);
        }
        else if (passangers[0].class == "comfort")
        {
            price_default = parseInt(flight.trip_information.comfort_place_price_default);
            price_child = parseInt(flight.trip_information.comfort_place_price_child);
            price_baby = parseInt(flight.trip_information.comfort_place_price_baby);
        }
        else
        {
            price_default = parseInt(flight.trip_information.business_place_price_default);
            price_child = parseInt(flight.trip_information.business_place_price_child);
            price_baby = parseInt(flight.trip_information.business_place_price_baby);
        }

        body += "<td>";
        for(var j = 0;j < passangers.length;j++)
        {
            var passanger = passangers[j];
            type = "";
            if (passanger.is_default == 1)
            {
                type = "Adult";
                result_price += price_default;
            }
            else if (passanger.is_child == 1)
            {
                type = "Child";
                result_price += price_child;
            }
            else
            {
                type = "Baby";
                result_price += price_baby;
            }

            if (passanger.has_luggage == 1)
            {
                result_price += parseInt(flight.trip_information.luggage_price);
            }

            body += "<b>" + type + "</b>" + ": " + passanger.firstName + " " + passanger.secondName + " " + passanger.lastName + ", <b>place number</b>: " + passanger.place_num + ", <b>passport number</b>: " + passanger.passport_num + "<br>";
        }
        body += "</td>";

        var flight_class = passangers[0].class;
        flight_class = flight_class[0].toUpperCase() + flight_class.substr(1);
        body += "<td>" + flight_class + ": " + result_price + " BYN" + "</td>";
        body += "<td> <form id='refuseForm" + flight.trip_information.trip_id + "'method='post' action='http://localhost/kursach/server/user/refuseFromFlight' onsubmit='checkRefuse()'> <input type='hidden' value='" + flight.trip_information.trip_id + "' name='trip_id'> <input type='hidden' value='" + flight.passangers_information[0].user_id + "' name='user_id'> <input type='submit' class='btn btn-danger' value='Refuse'> </form> <br> <form method='post' action='http://localhost/kursach/server/user/getTicketPdf' target='_blank'> <input type='hidden' value='" + flight.trip_information.trip_id + "' name='trip_id'> <input type='hidden' value='" + flight.passangers_information[0].user_id + "' name='user_id'> <input type='submit' class='btn btn-warning' value='Get ticket PDF'></form>  </td>";
        body += "<tr>";
    }
    body += "</table> <br><br><br>";
    document.getElementById("flightsInformation").innerHTML = body;
}

function checkRefuse()
{
    event.preventDefault();
    document.getElementById("trip_id_div").innerText = event.currentTarget.elements[0].value;
    document.getElementById("confirmRefuse").style.display = "block";
}

function refuse()
{
    document.getElementById("confirmRefuse").style.display = 'none';
    var trip_id = document.getElementById("trip_id_div").innerText;
    console.log(trip_id);
    document.getElementById("refuseForm" + trip_id).submit();
}

function changeFlightPeriod(event)
{
    var optionSelected = event.currentTarget.value;
    $.ajax({
        type: "POST",
        url: "http://localhost/kursach/server/user/getBookedFlights",
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        dataType : "json",
        data: {period: optionSelected},
        success: function (data)
        {
            var flights = data.flights;
            var keys = Object.keys(flights);
            if (flights.length == 0)
            {
                document.getElementById("flightsInformation").innerHTML = "<h2 style='margin-left: 30px'>No flights have been found for this period</h2>";
                document.getElementById("marginForFooter").style.display = "block";
            }
            else
            {
                document.getElementById("marginForFooter").style.display = "none";
                drawBookedFlights(flights, keys);
            }
        }
    });
}

function checkFlightParamsForm(event)
{
    event.preventDefault();
    var formEl = event.currentTarget;
    if (formEl.elements[0].value == "none")
    {
        document.getElementById("alert-text-fail").innerText = "Departure point not selected";
        document.getElementById("alertMessageFail").style.display = "block";
    }
    else if (formEl.elements[1].value == "none")
    {
        document.getElementById("alert-text-fail").innerText = "Arrival point not selected";
        document.getElementById("alertMessageFail").style.display = "block";
    }
    else
    {
        event.currentTarget.submit();
    }
}

function confirmTripDelete(event)
{
    event.preventDefault();
    var answer = confirm("Are you sure you want to delete this trip?");
    if (answer)
    {
        event.currentTarget.submit();
    }
}

function confirmFlightRefuse(event)
{
    event.preventDefault();
    var answer = confirm("Are you sure you want to refuse from this trip?");
    if (answer)
    {
        event.currentTarget.submit();
    }
}