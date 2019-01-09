<template>
    <sui-statistic in-group>
        <sui-statistic-value>{{ votes }}</sui-statistic-value>
        <sui-statistic-label>Votes</sui-statistic-label>
    </sui-statistic>
</template>

<script>
export default {
  props: ["votes"]
};
</script>
