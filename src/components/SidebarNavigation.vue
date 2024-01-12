<template>
    <ul
        class="widget-list widget-links sidebar-navigation navigation-level-3"
        :aria-label="$gettext('Dritte Navigationsebene')"
    >
        <li class="active">
            <RouterLink
                to="/"
                id="nav_lernkarten_index"
                activeClass="active"
                ariaCurrentValue="page"
            >
                {{ $gettext('Lernkarten') }}
            </RouterLink>
        </li>
    </ul>
</template>
