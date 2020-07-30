<template>
  <div id="app">
    <div id="nav">
      <span style="font-size:1.4em;color:green"> One Acre fund </span> <br>
       <router-link to="/postmortem"> check post-mortem report  </router-link>
    </div>
    <router-view/>
  </div>
</template>

<style lang="scss">
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}

#nav {
  padding: 30px;

  a {
    font-weight: bold;
    color: #a0c0b1;

    &.router-link-exact-active {
      color: #42b983;
    }
  }
}
</style>
