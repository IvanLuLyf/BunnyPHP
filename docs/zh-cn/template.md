# Template

BunnyPHP 的 `Template` 支持一套精简、可预期的模板语法子集（约定大于配置）。

## 变量输出

```text
{{ user.nickname }}
{{ user.profile.email }}
```

## 过滤器

支持链式过滤器：

```text
{{ user.nickname|escape }}
{{ user.nickname|e }}
{{ user.nickname|upper }}
{{ user.nickname|lower }}
{{ obj|json }}
{{ title|default('Untitled') }}
```

## 注释

```text
{# comment #}
```

## 控制结构

```text
{% if cond %}...{% endif %}
{% for item in list %}...{% endfor %}
{% include 'file.html' %}
```

## 约定

为了约定大于配置，模板**默认不自动转义**；输出到 HTML 时请显式使用 `|escape` / `|e`。

