function loadFreePlaces(trip_id, className)
{
    $.ajax({
        type: "POST",
        url: "http://localhost/kursach/server/flight/getFreePlaces",
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        dataType : "json",
        data: {trip_id: trip_id, class: className},
        success: function (data)
        {
            var selectValues = [];
            var i = 0;
            for(i = 0;i < data.taken_places.length;i++)
            {
                selectValues.push(new Option(data.taken_places[i], data.taken_places[i]));
            }
            last_id = document.querySelectorAll("select").length / 2;
            while (last_id > 0)
            {
                var numsCount = (selectValues.length % 4 == 0) ? (selectValues.length / 4) : (selectValues.length / 4 + 1);
                for (i = 1;i <= numsCount;i++)
                {
                    document.getElementById("num" + last_id).options.add(new Option(i, i));
                }
                document.getElementById("letter" + last_id).options.add(new Option("A", "A"));
                document.getElementById("letter" + last_id).options.add(new Option("B", "B"));
                document.getElementById("letter" + last_id).options.add(new Option("C", "C"));
                document.getElementById("letter" + last_id).options.add(new Option("D", "D"));
                last_id--;
            }
        }
    });
}

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
        event.currentTarget.submit();
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
            console.log(data);
        }
    });
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
            console.log(data);
        }
    });
}