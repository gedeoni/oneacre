<template>
  <div class="home">
    <b-loading :is-full-page="isFullPage" :active.sync="isLoading" :can-cancel="true"></b-loading>
     <b-button type="is-primary" @click="allcustomers">Customer Summaries</b-button> <b-button type="is-success" @click="debtcustomers()"> Customer Summaries with debt </b-button>
    <hr>
    <div class="row m-2"> 
      <div v-for="(customer, index) in customers"  :key="index" class="col-md-4 box" style="text-align:left"> 
        <p><strong>-Name:</strong> {{ customer.name }}</p> 
        <p><strong>-Season:</strong> {{ customer.seasonname }}</p> 
        <p><strong>-TotalRepaid:</strong> {{ customer.TotalRepaid }}</p> 
        <p><strong>-TotalCredit:</strong> {{ customer.TotalCredit }}</p> 
        <p style="color:red"><strong>-Owes:</strong> {{ customer.owes }}</p>  
        <router-link :to="'/pay/'+customer.customerid"><b-button type="is-info" v-if="customer.owes>0">Pay</b-button></router-link>
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
      customers: [],
      isLoading: false,
      isFullPage: true
    };
  },
  mounted(){
    this.axios.get("https://rcaconnect.com/oneAcre/api/service.php?customers=1").then((response) => {
        if(response.data.success) this.customers = response.data.customers
    })
  }, 
  methods: {
    allcustomers(){
      this.isLoading = true
      this.axios.get("https://rcaconnect.com/oneAcre/api/service.php?customers=1").then((response) => {
        if(response.data.success) this.customers = response.data.customers
        this.isLoading = false
     })
    }, 
    debtcustomers(){
      this.isLoading = true
      this.axios.get("https://rcaconnect.com/oneAcre/api/service.php?debtcustomers=1").then((response) => {
        if(response.data.success) this.customers = response.data.customers
        this.isLoading = false
     })
    }
  }
}
</script>
