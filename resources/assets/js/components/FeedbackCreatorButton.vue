<template>
  <sui-button fluid primary icon="plus" size="big" content="Créer un feedback" v-on:click="$emit('open-button')" />
</template>

<script>
export default {
  props: ["action"]
};
</script>

