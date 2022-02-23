// Root Component

const RootComponent = {};

// Create Vue App

const app = Vue.createApp({
  RootComponent,
  components:[],
  created() {
    window.document.title = this.title;
    this.initializeApp();
    //console.log(new Date(1642775713 * 1000));
  },
  mounted() {
    if (localStorage.user) {
      this.user = JSON.parse(localStorage.user);
      this.loggedIn = true;
      this.getUserData();
      this.adminPanel();
    }
    this.getFiles();
  },
  watch: {
    user(newUser) {
      localStorage.user = JSON.stringify(newUser);
    },
  },
  computed: {
    checkforlogin: function () {
      return this.loggedIn;
    },
    checkforuserdata: function () {
      return this.myUserdata;
    },
  },
  data() {
    return {
      title: "Web File Manager",
      nowDate: new Date().toDateString(),
      nowDateYear: new Date().getFullYear(),
      message: "Web File Manager",
      text: "Διαδικτυκός διαχειριστής αρχείων",
      loginText: "Πρέπει να συνδεθείτε πρώτα για να δείτε τα αρχεία σας!",
      loggedIn: false,
      loading: false,
      errored: null,
      data: null,
      user: null,
      myFiles: null,
      email: null,
      pass: null,
      modalMount: false,
      myUserdata: null,
      adminMount: false,
      status:{success:[],errors:[]},
      allusers:null
    };
  },
  methods: {
    adminPanel() {
      //register all calls for admins functions
      if (this.user.role == 'admin' || this.user.userRole == 'admin'){
        //console.log("admin functions");
        this.getAllUsers()
      }
    },
    getAllUsers(){
      axios
      .get(
        "./api/v1/users/read.php",
        {
          headers: {
            Authorization: this.user.token,
          },
        }
      )
      .then((response) => {
        if (response.status == 200) {
          //alert("User Data Updated!");
          this.allusers = response.data
        }
      })
      .catch((error) => {
        if (error.response.status == 401) {
          //alert(error.response.data.error);
          this.errored = error.response;
          this.status.errors.push(error.response.data.error)
          //this.logout();
        } else if (error.response.status == 404) {
          //alert(error.response.data.error);
          //this.logout();
          this.status.errors.push(error.response.data.error)
        }
      })
      .finally(() => {
        //console.log(this.myUserdata);
      });
    },
    updateUserCredentials() {
     // console.log(this.myUserdata);
      if (
        this.myUserdata.password == null ||
        this.myUserdata.password == "" ||
        this.myUserdata.password == "undefined"
      ) {
        axios
          .put(
            "./api/v1/users/update.php",
            {
              name: this.myUserdata.name,
              surname: this.myUserdata.surname,
              email: this.myUserdata.email,
              username: this.myUserdata.username,
            },
            {
              headers: {
                Authorization: this.user.token,
              },
            }
          )
          .then((response) => {
            if (response.status == 200) {
              //alert("User Data Updated!");
              this.status.success.push("User Data Updated!")
              this.user = JSON.parse(localStorage.user);
              this.user.userName = this.myUserdata.username;
              localStorage.user = JSON.stringify(this.user);
              this.getFiles();
            }
          })
          .catch((error) => {
            if (error.response.status == 401) {
              //alert(error.response.data.error);
              this.errored = error.response;
              this.status.errors.push(error.response.data.error)
              //this.logout();
            } else if (error.response.status == 404) {
              //alert(error.response.data.error);
              //this.logout();
              this.status.errors.push(error.response.data.error)
            }
          })
          .finally(() => {
            //console.log(this.myUserdata);
          });
      } else {
        axios
          .put(
            "./api/v1/users/update.php",
            {
              name: this.myUserdata.name,
              surname: this.myUserdata.surname,
              email: this.myUserdata.email,
              username: this.myUserdata.username,
              password: this.myUserdata.password,
            },
            {
              headers: {
                Authorization: this.user.token,
              },
            }
          )
          .then((response) => {
            if (response.status == 200) {
            //  alert("User Data Updated!");
            this.status.success.push("User Data Updated!")
            this.user = JSON.parse(localStorage.user);
            this.user.userName = this.myUserdata.username;
            localStorage.user = JSON.stringify(this.user);
            this.getFiles();
            }
          })
          .catch((error) => {
            if (error.response.status == 401) {
              //alert(error.response.data.error);
              this.errored = error.response;
              this.status.errors.push(error.response.data.error)
              this.logout();
            } else if (error.response.status == 404) {
              //alert(
              //  error.response.data.error + "\n" + "Log out and log in again!"
             // );
              //this.logout();
              this.status.errors.push(error.response.data.error)
            }
          })
          .finally(() => {
            //console.log(this.myUserdata);
          });
      }
    },
    getUserData() {
      if (!localStorage.user) {
        return;
      }
      if (localStorage.user) {
        this.user = JSON.parse(localStorage.user);
      }
      axios
        .get("./api/v1/users/single.php", {
          headers: {
            Authorization: this.user.token,
          },
        })
        .then((response) => {
          if (response.status == 200) {
            this.myUserdata = response.data;
          }
        })
        .catch((error) => {
          if (error.response.status == 401) {
            //alert(error.response.data.error);
            this.errored = error.response;
            this.errored = error.response;
            this.status.errors.push(error.response.data.error)
            this.logout();
          } else if (error.response.status == 404) {
           // alert(
          //    error.response.data.error + "\n" + "Log out and log in again!"
          //  );
            this.errored= error.response.data.error + "\n" + "Log out and log in again!"
            this.status.errors.push(error.response.data.error)
            //console.log(this.status.errors);
            //this.logout();
          }
        })
        .finally(() => {
          //console.log(this.myUserdata);
        });
    },
    mountModalfnc() {
      this.modalMount = !this.modalMount;
      //console.log("testtt");
      //console.log(this.modalMount);
    },
    adminModalfnc() {
      this.adminMount = !this.adminMount;
      //console.log("testtt");
      //console.log(this.modalMount);
    },
    logout() {
      localStorage.removeItem("user");
      this.loggedIn = false;
      this.status.success.push("User Logged Out!")
    },
    loggin() {
      this.loading = true;
      axios
        .post("./api/v1/users/login.php", {
          email: this.email,
          password: this.pass,
        })
        .then((response) => {
          if (response.status == 200) {
            this.loggedIn = true;
            this.user = response.data;
            this.status.success.push("Welcome Back: "+ this.user.userName)
          }
        })
        .catch((error) => {
          //alert(error.response.data.error);
          this.errored = error.response;
          this.status.errors.push(error.response.data.error)
          //console.log(this.status.errors);
        })
        .finally(() => {
          this.loading = false;
          this.getFiles();
          this.getUserData();
          this.adminPanel();
        });
    },
    handleSubmit(email, pass) {
      this.email = email;
      this.pass = pass;
      this.loggin();
    },
    initializeApp() {
      axios
        .get("./api/v1/users/initialize.php")
        .then((response) => {})
        .catch((error) => {})
        .finally(() => {});
    },
    getFiles() {
      if (!localStorage.user) {
        return;
      }
      axios
        .get("./api/v1/files/read.php?path=/", {
          headers: {
            Authorization: this.user.token,
          },
        })
        .then((response) => {
          if (response.status == 200) {
            this.myFiles = response.data;
          }
        })
        .catch((error) => {
          if (error.response.status == 401) {
            //alert(error.response.data.error);
            this.errored = error.response;
            this.status.errors.push(error.response.data.error)
            this.logout();
          } else if (error.response.status == 404) {
            //alert(
           //   error.response.data.error + "\n" + "Log out and log in again!"
           // );
           this.status.errors.push(error.response.data.error)
            //this.logout();
          }
        })
        .finally(() => {});
    },
  },
});

// Components

app.component("my-files", {
  props: ["modelValue", "logout","st"],
  data() {
    return {
      linkElement: null,
      errored: null,
      user: null,
      history: [],
      homeVal: "/",
    };
  },
  computed: {
    myFiles: {
      get() {
        //console.log(this.modelValue);
        return this.modelValue;
      },
      set(val) {
        this.$emit("update:modelValue", val);
      },
    },
    status: {
      get() {
        //console.log(this.modelValue);
       // console.log(this.st);
        return this.st;
      },
      set(val) {
        //console.log(val);
        this.$emit("update:st", val);
      },
    },
  },
  methods: {
    home() {
      this.history = []
      this.navigationRequest(this.homeVal);
    },
    getBack() {
      this.history.pop();
      value = this.history.at(-1);
      if (!value || value == "undefined") {
        value = "/";
      }
     // console.log(this.history);
      this.navigationRequest(value);
    },
    navigation: function (event) {
      this.user = JSON.parse(localStorage.user);
      this.linkElement = event.target;
      let val = null;
      if (this.linkElement.type == "folder") {
        event.preventDefault();
        val = this.linkElement.attributes[2].value;
        pos = val.indexOf(this.user.userName);
        val = val.substr(pos);
        val = val.replace(this.user.userName, "");
        //console.log(val);
        this.history.push(val);
        // console.log(this.history);
        val = this.navigationRequest(val);
      }
    },
    navigationRequest: function (val) {
      if (!localStorage.user) {
        return;
      }
      this.user = JSON.parse(localStorage.user);
      axios
        .get("./api/v1/files/read.php?path=" + val, {
          headers: {
            Authorization: this.user.token,
          },
        })
        .then((response) => {
          if (response.status == 200) {
            this.myFiles = response.data;
          }
        })
        .catch((error) => {
          if (!error) {
            return;
          }
          if (error.response.status == 401) {
            //alert(error.response.data.error);
            this.errored = error.response;
            this.status.errors.push(error.response.data.error)
            this.logout();
          } else if (error.response.status == 404) {
            //alert(error.response.data.error);
            this.status.errors.push(error.response.data.error)
            this.history.pop()
            //console.log(this.history);
          }
        })
        .finally(() => {
          //console.log(this.history);
        });
    },
  },
  template: `
  <div class="table-responsive">
    <button type="button" class="btn btn-light p-1 m-1" id="back" @click="getBack">Πίσω</button>
    <button type="button" class="btn btn-light p-1 m-1" id="create" @click="home">Αρχική</button>
    <button type="button" class="btn btn-light p-1 m-1" id="create">Δημιουργία</button>
    <button type="button" class="btn btn-light p-1 m-1" id="delete">Διαγραφή</button>
    <button type="button" class="btn btn-light p-1 m-1" id="move">Μετακίνηση</button>
    <button type="button" class="btn btn-light p-1 m-1" id="copy">Αντιγραφή</button>
    <button type="button" class="btn btn-light p-1 m-1" id="copy">Ανέβασμα</button>
    <table class="table table-hover" id="tbl">
        <thead>
            <tr>
              <th>Αρχεία - Φακέλοι ({{this.myFiles.length}})</th>
              <th>Είδος Αρχείου</th>
              <th>Μέγεθος</th>
              <th>Ημερομηνία</th>
              <th>Ιδιοκτήτης</th>
            </tr>
          </thead>    
        <tbody>
          <tr v-for="(f ,index) in myFiles" :key="f.id"><td @click="navigation" style="cursor: pointer;"><img class="folder" :src="f.icon" style="width: 20px; height: 20px; margin-right: 6px;" /><a class="mylinks" :id="f.id" :mypath="f.BaseDir" :type="f.type" :href="f.BaseDir" target="_blank" style="text-decoration:none;color:black">{{f.filename}}</a></td><td>{{f.format}}</td><td>{{f.size}}</td><td>{{f.lastDate}}</td><td>{{f.owner}}</td></tr>
        </tbody>
    </table>
  </div>
    `,
});

app.component("custom-form", {
  props: ["modelValue", "mytype", "myplaceholder"],
  computed: {
    inputValue: {
      get() {
        return this.modelValue;
      },
      set(newData) {
        this.$emit("update:modelValue", newData);
        // console.log(newData);
      },
    },
  },
  template: `
        <input class="form-control me-2" :type="mytype" :placeholder="myplaceholder" v-model="inputValue" />
    `,
});

app.component("navbar", {
  props: [
    "title",
    "formEmail",
    "formPassword",
    "fnc",
    "login",
    "logout",
    "user",
    "mymodal",
    "adminp",
    "st"
  ],
  components: ["custom-form"],
  data() {
    return {
      data: {
        emailVal: null,
        passVal: null,
      },
    };
  },
  computed:{
    status: {
      get() {
        return this.st;
      },
      set(val) {
        this.$emit("update:st", val);
      },
    },
  },
  methods: {
    handleSubmit(event) {
      event.preventDefault();
      if (
        (this.data.emailVal == null && this.data.passVal == null) ||
        (this.data.emailVal == "" && this.data.passVal == "")
      ) {
        this.status.errors.push("Check the inputs!")
        return;
      }
      //console.log(this.data.emailVal, this.data.passVal);
      this.fnc(this.data.emailVal, this.data.passVal);
    },
  },
  template: `
      <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="."> <img src="assets/icon.png" alt="Logo" style="width:25px;">
              {{title}}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mob-navbar" aria-label="Toggle">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse " id="mob-navbar">
                <ul class="navbar-nav mb-2 mb-lg-0 ms-2 ms-auto pe-2" v-if="login">
                    <li class="nav-item dropdown">
                        <a :key="user.userId" :id="user.userId" class="nav-link dropdown-toggle fw-bolder" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Welcome: {{user.userName.toUpperCase()}}</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#" @click="mymodal">My Profile</a></li>
                            <li v-if="user.userRole == 'admin'"><a class="dropdown-item" href="#" @click="adminp">Admin Panel</a></li>
                            <li class="nav-item p-1 ms-2">
                              <button type="button" class="btn btn-warning btn-sm" id="logout" @click="logout">Log Out</button>
                            </li>
                        </ul>
                    </li>
                </ul>
                <form class="d-flex position-absolute end-0 me-1 p-1 myform" id="loginForm" v-if="!login">
                  <custom-form v-model="data.emailVal" mytype="email" myplaceholder="Email"></custom-form>
                  <custom-form v-model="data.passVal"  mytype="password" myplaceholder="Password"></custom-form>
                  <button class="btn btn-primary" id="submitBtn" @click="handleSubmit">Login</button>
                </form>
            </div>
        </div>
    </nav>
    `,
});

app.component("custom-footer", {
  props: ["author", "year"],
  template: `
      <footer class="d-flex flex-row-reverse mb-0 pb-0"><small class="text-white fw-bolder"><i>&#169;{{year}} {{author}}</i></small></footer>
    `,
});

app.component("loading-spinner", {
  template: `
    <div class="text-center mt-5">
      <div class="spinner-border" role="status">
      </div>
    </div>
  `,
});

app.component("my-profile", {
  props: ["modal", "modelValue", "myfnc", "updt"],
  data() {
    return {
      test: this.modal,
      status:0,
    };
  },
  mounted(){
    const mod = this.$refs.mdl
   // mod.classList.value += " blury"
  },
  computed: {
    inputValue: {
      get() {
        return this.modelValue;
      },
      set(newData) {
        this.$emit("update:modelValue", newData);
        // console.log(newData);
      },
    },
  },
  watch: {},
  methods: {
    formSumbit(e) {
      e.preventDefault();
    },
    validation() {},
  },
  template: `
      <div
  class="modal show"
  id="myModal"
  style="display: block"
  aria-modal="true"
  role="dialog"
  ref="mdl"
  tabindex="-1"
>
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title"><b>My Profile</b></h4>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          @click="myfnc"
        ></button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        <div class="container ms-1 ps-1">
          <form class="row g-3 needs-validation" novalidate="" @submit.prevent="updt(name, surname, username, email, password)">
            <div class="col-md-4">
              <label for="validationCustom01" class="form-label"
                >First name</label
              ><input
                type="text"
                class="form-control"
                id="validationCustom01"
                required=""
                v-model="inputValue.name"
              />
              <div class="valid-feedback">Looks good!</div>
            </div>
            <div class="col-md-4">
              <label for="validationCustom02" class="form-label"
                >Last name</label
              ><input
                type="text"
                class="form-control"
                id="validationCustom02"
                required=""
                 v-model="inputValue.surname"
              />
              <div class="valid-feedback">Looks good!</div>
            </div>
            <div class="col-md-4">
              <label for="validationCustomUsername" class="form-label"
                >Username</label
              >
              <div class="input-group has-validation">
                <span class="input-group-text" id="inputGroupPrepend">@</span
                ><input
                  type="text"
                  class="form-control"
                  id="validationCustomUsername"
                  aria-describedby="inputGroupPrepend"
                  required=""
                  v-model="inputValue.username"
                />
                <div class="invalid-feedback">Please choose a username.</div>
              </div>
            </div>
            <div class="col-md-12">
              <label for="validationCustom03" class="form-label">Password</label
              ><input
                type="password"
                class="form-control"
                id="validationCustom03"
                required=""
                autocomplete="off"
                placeholder="Your New Password"
                v-model="inputValue.password"
              />
              <div class="invalid-feedback">Please provide a valid city.</div>
            </div>
            <div class="col-12">
              <label for="validationCustomUsername" class="form-label"
                >Email</label
              >
              <div class="input-group has-validation">
                <span class="input-group-text" id="inputGroupPrepend">@</span
                ><input
                  type="text"
                  class="form-control"
                  id="validationCustomUsername"
                  aria-describedby="inputGroupPrepend"
                  required=""
                  v-model="inputValue.email"
                />
                <div class="invalid-feedback">Please choose a username.</div>
              </div>
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit">Update</button>
            </div>
          </form>
        </div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="myfnc">
          Close
        </button>
      </div>
    </div>
  </div>
</div>
    `,
});

app.component("admin-panel", {
  props: ["modalt", "myfnc", "users"],
  data() {
    return {
      test: this.modalt,
    };
  },
  methods:{
  },
  template: `
      <div
  class="modal show"
  id="myModal"
  style="display: block;"
  aria-modal="true"
  role="dialog"
  tabindex="-1"
>
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title"><b>Admin Panel</b></h4>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          @click="myfnc"
        ></button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
      <div class="actions">
        <h4 class="title">Actions:</h4>      
        <button type="button" class="btn btn-primary mybtn">Create</button>
      </div>
      <hr/>
      <div class="table-responsive">
      <table class="table table-striped table-hover">
          <thead>
          <tr>
            <th scope="col">Id</th>
            <th scope="col">Username</th>
            <th scope="col">First</th>
            <th scope="col">Last</th>
            <th scope="col">Email</th>
            <th scope="col">Role</th>
            <th scope="col">Created</th>
            <th scope="col">Last IP</th>
            <th scope="col">Last Device</th>
            <th scope="col">Last Login</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users">
            <th scope="row" data-bs-toggle="tooltip" :title="u.baseDir">{{u.id}}</th>
            <td>{{u.username}}</td>
            <td>{{u.name}}</td>
            <td>{{u.surname}}</td>
            <td>{{u.email}}</td>
            <td>{{u.role}}</td>
            <td>{{u.created_at}}</td>
            <td>75.64.23.1</td>
            <td>android os 4.8.8</td>
            <td>01/02/2022</td>
            <td>
            <div class="d-block">
              <button type="button" class="btn btn-warning">Edit</button>
            </div>
            <div class="d-block mt-2">
              <button type="button" class="btn btn-danger">Delete</button>
            </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="myfnc">
          Close
        </button>
      </div>
    </div>
  </div>
</div>
    `,
});

app.component("mynotification", {
  props: ["alerttype", "title", "icon", "timer"],
  data() {
    return {
      mytype: "alert alert-" + this.alerttype + " alert-dismissible fade show",
      info: "#info-fill",
      success: "#check-circle-fill",
      warning: "#exclamation-triangle-fill",
      timerCount: this.timer,
    };
  },
  watch: {
    timerCount: {
      handler(value) {
        if (value > 0) {
          setTimeout(() => {
            this.timerCount--;
          }, 1000);
        }
      },
      immediate: true, // This ensures the watcher is triggered upon creation
    },
  },
  computed: {
    checktimer: function () {
      return this.timerCount;
    },
  },
  template: `
    <div v-if="!checktimer == 0" :class="mytype" role="alert" style="width:300px;height:50px;float:right;overflow: hidden;white-space: nowrap;">
      <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:"><use :xlink:href=" icon == 'info' ? this.info : icon == 'warning' ? this.warning : this.success "/></svg>
        {{title}}
    </div>
  `,
});

/*
app.component("createuser", {
  props: ["modalt", "myfnc", "users"],
  data() {
    return {
      test: this.modalt,
    };
  },
  mounted(){
 
  },
  computed: {

  },
  watch: {},
  methods: {
    formSumbit(e) {
      e.preventDefault();
    },
    validation() {},
  },
  template: `
      <div
  class="modal show"
  id="myModal"
  style="display: block"
  aria-modal="true"
  role="dialog"
  ref="mdl"
  tabindex="-1"
>
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title"><b>Create User</b></h4>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          @click="myfnc"
        ></button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        <div class="container ms-1 ps-1">
          <form class="row g-3 needs-validation" novalidate="" @click="formSubmit">
            <div class="col-md-4">
              <label for="validationCustom01" class="form-label"
                >First name</label
              ><input
                type="text"
                class="form-control"
                id="validationCustom01"
                required=""
             
              />
              <div class="valid-feedback">Looks good!</div>
            </div>
            <div class="col-md-4">
              <label for="validationCustom02" class="form-label"
                >Last name</label
              ><input
                type="text"
                class="form-control"
                id="validationCustom02"
                required=""
               
              />
              <div class="valid-feedback">Looks good!</div>
            </div>
            <div class="col-md-4">
              <label for="validationCustomUsername" class="form-label"
                >Username</label
              >
              <div class="input-group has-validation">
                <span class="input-group-text" id="inputGroupPrepend">@</span
                ><input
                  type="text"
                  class="form-control"
                  id="validationCustomUsername"
                  aria-describedby="inputGroupPrepend"
                  required=""
                
                />
                <div class="invalid-feedback">Please choose a username.</div>
              </div>
            </div>
            <div class="col-md-12">
              <label for="validationCustom03" class="form-label">Password</label
              ><input
                type="password"
                class="form-control"
                id="validationCustom03"
                required=""
                autocomplete="off"
                placeholder="Your New Password"
              
              />
              <div class="invalid-feedback">Please provide a valid city.</div>
            </div>
            <div class="col-12">
              <label for="validationCustomUsername" class="form-label"
                >Email</label
              >
              <div class="input-group has-validation">
                <span class="input-group-text" id="inputGroupPrepend">@</span
                ><input
                  type="text"
                  class="form-control"
                  id="validationCustomUsername"
                  aria-describedby="inputGroupPrepend"
                  required=""
                 
                />
                <div class="invalid-feedback">Please choose a username.</div>
              </div>
              </div>
              <div class="col-12">
              <div class="col-12">
                <select class="form-select" aria-label="Default select example">
                  <option selected>Choose user role</option>
                  <option value="user">User</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
              <button class="btn btn-primary mt-4" type="submit">Create</button>
            </div>
          </form>
        </div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="myfnc" >
          Close
        </button>
      </div>
    </div>
  </div>
</div>
    `,
});

*/

// Mount App
app.mount("#app");
