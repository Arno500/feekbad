<template>
  <div>
    <nav>
      <div class="ui top menu">
        <a class="item" href="/">
          Feedback
        </a>
        <div class="right menu">

          <div v-if="this.$store.state.connected" class="item">Votes restants : <span id="uservotes"> {{this.$store.state.remainingVotes}}</span></div>
          <a v-if="!this.$store.state.connected" class="item" href="login">Connexion / Inscription</a>
          <a v-else class="item" href="logout">Se déconnecter</a>
        </div>
      </div>
    </nav>
    <main>
      <div class="parent">
        <transition :name="transitionName" mode="out-in">
          <router-view class="child-view"></router-view>
        </transition>
      </div>
    </main>
  </div>
</template>


<script>
export default {
  data() {
    return {
      transitionName: "slide-left"
    };
  },
  watch: {
    $route(to, from) {
      const toDepth = to.path.split("/").length;
      const fromDepth = from.path.split("/").length;
      this.transitionName = toDepth < fromDepth ? "slide-right" : "slide-left";
    }
  }
};
</script>