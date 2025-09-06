<script>
    function goto(v) {
        location = v;
    }
</script>

<div class="subheader">
    <div onclick="goto('/app');"><p>Home</p></div>
    <div onclick="goto('/app/apps');"><p>Apps</p></div>
    <div onclick="goto('/app/leaderboards');"><p>Leaderboards</p></div>
    <div onclick="goto('/app/webhooks');"><p>Webhooks</p></div>
    <div onclick="goto('/app/groups');"><p>Groups</p></div>
</div>