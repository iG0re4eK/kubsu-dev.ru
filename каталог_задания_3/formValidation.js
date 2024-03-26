document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("myForm");
  const userName = document.getElementById("fullname");
  const email = document.getElementById("email");
  const phone = document.getElementById("phone");
  const dob = document.getElementById("dob");
  const gender = document.querySelectorAll('input[name="gender"]');
  const languages = document.getElementById("languages");
  const bio = document.getElementById("bio");
  const check = document.getElementById("contractt");

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    validateInputs();
  });

  const setError = (element, message) => {
    const inputControl = element.parentElement;
    const errorDisplay = inputControl.querySelector(".error");

    errorDisplay.innerText = message;
    inputControl.classList.add("error");
    inputControl.classList.remove("success");
  };

  const setSuccess = (element) => {
    const inputControl = element.parentElement;
    const errorDisplay = inputControl.querySelector(".error");

    errorDisplay.innerText = "";
    inputControl.classList.remove("error");
    inputControl.classList.add("success");
  };

  const isValidEmail = (email) => {
    const re =
      /^(([^<>()[\].,;:\s@"]+(\.[^<>()[\].,;:\s@"]+)*)|(".+"))@(([^<>()[\].,;:\s@"]+\.)+[^<>()[\].,;:\s@"]{2,})$/;
    return re.test(String(email).toLowerCase());
  };

  const isValidName = (userName) => {
    const re = /^[а-яА-ЯёЁa-zA-Z]+ [а-яА-ЯёЁa-zA-Z]+ ?[а-яА-ЯёЁa-zA-Z]+$/;
    return re.test(String(userName).toLowerCase());
  };

  const isValidPhone = (phone) => {
    const re = /^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/;
    return re.test(String(phone).toLowerCase());
  };

  const validateInputs = () => {
    const nameValue = userName.value.trim();
    const emailValue = email.value.trim();
    const phoneValue = phone.value.trim();
    const dobValue = dob.value.trim();
    const genderValue = [...gender].some((radio) => radio.checked);
    const languagesValue = languages.value.length > 0;
    const bioValue = bio.value.trim();
    const isChecked = check.checked;

    if (emailValue === "") {
      setError(email, "Необходим email");
    } else if (!isValidEmail(emailValue)) {
      setError(email, "Введен неверный email");
    } else {
      setSuccess(email);
    }

    if (nameValue === "") {
      setError(userName, "Необходимо ФИО");
    } else if (!isValidName(nameValue)) {
      setError(userName, "Введено неверное ФИО");
    } else {
      setSuccess(userName);
    }

    if (phoneValue === "") {
      setError(phone, "Необходим номер телефона");
    } else if (!isValidPhone(phoneValue)) {
      setError(phone, "Введен неверный номер телефона");
    } else {
      setSuccess(phone);
    }

    if (dobValue === "") {
      setError(dob, "Необходима дата рождения");
    } else {
      setSuccess(dob);
    }

    if (!genderValue) {
      setError(gender[0], "Необходимо выбрать пол");
    } else {
      setSuccess(gender[0]);
    }

    if (!languagesValue) {
      setError(
        languages,
        "Необходимо выбрать хотя бы один язык программирования"
      );
    } else {
      setSuccess(languages);
    }

    if (bioValue === "") {
      setError(bio, "Необходимо заполнить биографию");
    } else {
      setSuccess(bio);
    }

    if (!isChecked) {
      setError(check, "Необходимо согласие");
    } else {
      setSuccess(check);
    }

    if (
      isValidEmail(emailValue) &&
      isValidName(nameValue) &&
      isValidPhone(phoneValue) &&
      dobValue !== "" &&
      genderValue &&
      languagesValue &&
      bioValue !== "" &&
      isChecked
    ) {
      // Если все проверки пройдены, отправить данные на сервер
      form.submit(); // Отправляем форму на сервер
    }
  };
});
