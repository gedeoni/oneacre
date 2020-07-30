<template>
  <div class="home">
    <b-loading :is-full-page="isFullPage" :active.sync="isLoading" :can-cancel="true"></b-loading>
    <router-link to="/"><b-button type="is-primary"> Home </b-button> </router-link>
    
    <hr>
    <b-modal :active.sync="isImageModalActive">
        <div style="min-height:200px">
            <strong> Repayment Records </strong>
            <hr>
            <div v-for="(m, index) in msg" :key="index"> 
                <p> {{ m }} </p> 
            </div>
        </div>
    </b-modal>
     <div class="row"> 
         <div class="col-md-4 offset-md-4">
            <b-field label="Specify amount (RWF)">
                <b-input 
                    v-model="amount"
                    placeholder="Number"
                    type="number">
                </b-input> 
            </b-field>
            <b-button type="is-info" v-if="amount>0" @click="pay"> Pay </b-button>
            <hr>
        </div>
     </div>


    <span class="border-bottom"> <strong> Debt Summary </strong></span>
    <div class="row m-3"> 
      <div v-for="(customer, index) in customers"  :key="index" class="col-md-4 box" style="text-align:left"> 
        <p><strong>-Name:</strong> {{ customer.name }}</p> 
        <p><strong>-Season:</strong> {{ customer.seasonname }}</p> 
        <p><strong>-TotalRepaid:</strong> {{ customer.TotalRepaid }}</p> 
        <p><strong>-TotalCredit:</strong> {{ customer.TotalCredit }}</p> 
        <p style="color:red"><strong>-Owes:</strong> {{ customer.owes }}</p>  
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
