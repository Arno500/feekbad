<template>
  <div class="creator-wrapper">
    <transition name="component-slide" v-on:enter="enter" v-on:leave="leave">

      <component :is="feedbackCreatorDynamic" v-on:feedback-sent="closeEditor" v-on:open-button="showEditor" v-on:close-editor="closeEditor"></component>

    </transition>
  </div>

</template>

<script>
export default {
  data: function() {
    return {
      feedbackCreatorDynamic: "feedback-creator-button"
    };
  },
  methods: {
    showEditor: function() {
      this.feedbackCreatorDynamic = "feedback-creator-form";
    },
    closeEditor: function() {
      this.feedbackCreatorDynamic = "feedback-creator-button";
    },
    enter: function(event) {
      if (this.feedbackCreatorDynamic === "feedback-creator-form") {
        event.parentNode.style.height = "15rem";
      } else {
        event.parentNode.style.height = "4rem";
      }
      return;
    },
    leave: function(event) {
      this.enter(event);
      return;
    }
  }
};
</script>

<style scoped>
.component-slide-enter-active,
.component-slide-leave-active {
  transition: all 0.5s ease;
  /* transform: translate3d(0, 0, 0); */
}

.component-slide-leave-to {
  opacity: 0;
  transform: translate3d(-100%, 0, 0);
}

.component-slide-enter {
  transform: translate3d(100%, 0, 0);
  opacity: 0;
}

button.component-slide-enter {
  margin-bottom: 12rem;
}

button.component-slide-leave-to {
  margin-bottom: 12rem;
}

button.component-slide-enter-to {
  margin-bottom: initial;
}

.ui.form,
button.fluid.primary {
  transition: all 0.5s ease;
  position: absolute;
}

.ui.form {
  margin-bottom: 5rem;
  width: 100%;
}

.creator-wrapper {
  position: relative;
  margin-top: 2rem;
  margin-bottom: 2rem;
  height: 2rem;
  transition: all 0.6s cubic-bezier(0.07, 0.56, 0.4, 1.01);
}

/* .ui.primary.button {
  -webkit-box-shadow: 0px 34px 27px -10px rgba(34, 36, 38, 0.22) inset;
  box-shadow: 0px 34px 27px -10px rgba(34, 36, 38, 0.22) inset;
} */
</style>
