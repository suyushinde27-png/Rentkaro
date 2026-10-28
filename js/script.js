
function searchVehicle() {
  const location = document.getElementById("pickupLocation")?.value.trim();
  const pickup = document.getElementById("pickupDate")?.value;
  const returnDate = document.getElementById("returnDate")?.value;
  const type = document.getElementById("vehicleType")?.value || "all";

  if (!location) {
    alert("Please enter a pickup location.");
    return;
  }
  if (pickup && returnDate && new Date(returnDate) < new Date(pickup)) {
    alert("Return date cannot be before the pickup date.");
    return;
  }
  const params = new URLSearchParams({ location, type });
  if (pickup) params.set("pickup", pickup);
  if (returnDate) params.set("return", returnDate);
  window.location.href = "vehicle-listing.html?" + params.toString();
}

async function loginUser() {

    const email = document.getElementById("email")?.value.trim();
    const password = document.getElementById("password")?.value;

    if (!email || !password) {
        return alert("Please fill in all fields.");
    }

    try {
        const response = await fetch("./backend/login.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                email: email,
                password: password
            })
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            window.location.href = "dashboard.html";
        } else {
            alert(result.message);
        }

    } catch (error) {
        console.error(error);
        alert("Unable to connect to the server.");
    }
}

async function signupUser() {

    const name = document.getElementById("name")?.value.trim();
    const email = document.getElementById("signupEmail")?.value.trim();
    const mobile = document.getElementById("mobile")?.value.trim();
    const password = document.getElementById("signupPassword")?.value;
    const confirmPassword = document.getElementById("confirmPassword")?.value;

    if (!name || !email || !mobile || !password || !confirmPassword) {
        return alert("Please fill in all fields.");
    }

    if (!/^\d{10}$/.test(mobile)) {
        return alert("Please enter a valid 10-digit mobile number.");
    }

    // Password validation: at least 6 characters with at least one number
    if (password.length < 6 || !/[0-9]/.test(password)) {
        return alert("Password must be at least 6 characters and contain at least one number.");
    }

    if (password !== confirmPassword) {
        return alert("Passwords do not match.");
    }

    try {
        const response = await fetch("./backend/signup.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                name: name,
                email: email,
                mobile: mobile,
                password: password
            })
        });

        const result = await response.json();

if (result.success) {
    alert(result.message);
    window.location.href = "login.html";
} else {
    alert(result.message);
}

    } catch (error) {
        console.error(error);
        alert("Unable to connect to the server.");
    }
}
async function adminLogin() {
  const email = document.getElementById("adminEmail")?.value.trim();
  const password = document.getElementById("adminPassword")?.value;
  if (!email || !password) return alert("Please fill in all fields.");

  try {
    const response = await fetch("./admin/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ email, password })
    });

    const result = await response.json();

    if (result.success) {
      alert(result.message);
      window.location.href = "admin-dashboard.html";
    } else {
      alert(result.message);
    }
  } catch (error) {
    console.error(error);
    alert("Unable to connect to server.");
  }
}

function saveProfile() {
  const name = document.getElementById("edit-name")?.value.trim();
  const email = document.getElementById("edit-email")?.value.trim();
  const phone = document.getElementById("edit-phone")?.value.trim();
  if (!name || !email || !phone) return alert("Please fill in all fields.");
  alert("Profile Updated Successfully!");
  window.location.href = "dashboard.html";
}
