<template>
  <div class="home">
    <b-loading :is-full-page="isFullPage" :active.sync="isLoading" :can-cancel="true"></b-loading>
    <router-link to="/"><b-button type="is-primary"> Home </b-button> </router-link>
    
    <hr>
    <div class="col-md-8 offset-md-2">
        POST-MORTEM 
        <hr>
        <div style="text-align:left"> 
            <p class="font-weight-bold"> Project status: </p>
            - Project was successfully completed. All functionalities were successfully implemented <br>
            - Link to the project's web site: <a href="https://gedeon-oneacre.herokuapp.com"> gedeon-oneacre.herokuapp.com </a>
            - Link to the project's git: <a href="https://github.com/gedeoni/oneacre"> gedeon-oneacre.git  </a>
            <br><br><br>
            <p class="font-weight-bold"> Estimate on the outstanding work:  </p>
            - No outstanding work
            <br><br><br>
            <p class="font-weight-bold">Successes/what went well </p>
            - I managed to finish the project with in the allocated time
            <br><br><br>
            <p class="font-weight-bold">Bumps/what you wished went better </p>
            - I could have done more with the UI
        </div>
    </div>
  </div>
</template>

<script>
// @ is an alias to /src

export default {
  name: 'Home',
  data() {
    const today = new Date();
    return {
      isImageModalActive: false,
      customers: [],
      debtcustomers: [],
      isLoading: false,
      isFullPage: true, 
      amount: 0, 
      msg: []
    };
  },
  created(){
      this.axios.get(`https://rcaconnect.com/oneAcre/api/service.php?customerid=${this.$route.params.id}`).then((response) => {
        if(response.data.success) this.customers = response.data.customers
    })
  }, 
  methods: {
    pay(){
        this.isLoading = true
        this.axios.post("https://rcaconnect.com/oneAcre/api/service.php", {
          paying: this.amount,
          customerid: this.$route.params.id
        }).then((response) => {
            this.isLoading = false
            this.updated()

            this.msg = response.data.res.split("\n"); 
            this.isImageModalActive = true
        })
    }, 
    updated(){
        this.axios.get(`https://rcaconnect.com/oneAcre/api/service.php?customerid=${this.$route.params.id}`).then((response) => {
        if(response.data.success) this.customers = response.data.customers
    })
    }
  }
}
</script>
