function checkForm(event)
{
    event.preventDefault();
    var selectsCount = document.querySelectorAll("select").length / 2;
    var placesNum = [];
    for(var i = 1;i <= selectsCount;i++)
    {
        placesNum.push(document.getElementById("letter" + i).value + document.getElementById("num" + i).value);
    }
    if (new Set(placesNum).size != placesNum.length)
    {
        alert("All the places chosen must be different");
    }
    else
    {
        $.ajax({
            type: "POST",
            url: "http://localhost/kursach/server/user/registerForFlight",
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            dataType : "json",
            data: $('form').serialize(),
            success: function (data)
            {
                alert(data.message);
                window.location.href = "http://localhost/kursach/server/";
            }
        });
    }
}

function checkIfAuthorized(event, user_id)
{
    event.preventDefault();
    if (!user_id)
    {
        alert("You are not authorized");
        window.location.href = "http://localhost/kursach/server/login";
    }
    else
    {
        event.currentTarget.submit();
    }
}

function checkLogout(event)
{
    event.preventDefault();
    var answer = confirm("Are you sure you want to exit your account?");
    if (answer)
    {
        event.currentTarget.submit();
    }
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
            alert(data.message);
            if (data.message != "Wrong credentials")
            {
                window.location.href = "http://localhost/kursach/server/";
            }
        }
    });
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

    $.ajax({
        type: "POST",
        url: "http://localhost/kursach/server/user/register",
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        dataType : "json",
        data: {login: login, password: password, firstName: firstName, secondName: secondName, lastName: lastName, email: email, phone: phone, login: login, password: password},
        success: function (data)
        {
            alert(data.message);
            if (data.message != "User with this login already exists")
            {
                window.location.href = "http://localhost/kursach/server/";
            }
        }
    });
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
            }
            else
            {
               drawBookedFlights(flights, keys);
            }
        }
    });
}

function drawBookedFlights(flights, keys)
{
    var i = 0;
    var body = "";
    body += "<table border='1'><tr><th>#</th><th>Departure</th><th>Arrival</th><th>Passangers</th><th>Flight class : Price</th><th>Actions</th></tr>";
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
        body += "<td> <form method='post' action='http://localhost/kursach/server/user/refuseFromFlight' onsubmit='confirmFlightRefuse(event)'> <input type='hidden' value='" + flight.trip_information.trip_id + "' name='trip_id'> <input type='hidden' value='" + flight.passangers_information[0].user_id + "' name='user_id'> <input type='submit' value='Refuse'></form> <br> <form method='post' action='http://localhost/kursach/server/user/getTicketPdf' target='_blank'> <input type='hidden' value='" + flight.trip_information.trip_id + "' name='trip_id'> <input type='hidden' value='" + flight.passangers_information[0].user_id + "' name='user_id'> <input type='submit' value='Get ticket PDF'></form>  </td>";
        body += "<tr>";
    }
    body += "</table>";
    document.getElementById("flightsInformation").innerHTML = body;
}

function changeFlightPeriod(event)
{
    var optionSelected = event.currentTarget.value;
    console.log(optionSelected);
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
                document.getElementById("flightsInformation").innerHTML = "<h2>No flights have been found for this period</h2>";
            }
            else
            {
                drawBookedFlights(flights, keys);
            }
        }
    });
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