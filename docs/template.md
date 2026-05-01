# Template

BunnyPHP `Template` supports a small, familiar template syntax subset.

## Variables

```text
{{ user.nickname }}
{{ user.profile.email }}
```

## Filters

Filters can be chained:

```text
{{ user.nickname|escape }}
{{ user.nickname|e }}
{{ user.nickname|upper }}
{{ user.nickname|lower }}
{{ obj|json }}
{{ title|default('Untitled') }}
```

## Comments

```text
{# comment #}
```

## Control structures

```text
{% if cond %}...{% endif %}
{% for item in list %}...{% endfor %}
{% include 'file.html' %}
```

## Convention

By convention (less config), templates **do not auto-escape**. Use `|escape` / `|e` when output goes into HTML.

