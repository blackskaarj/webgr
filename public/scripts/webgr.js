
function confirmDelete(js_message,js_url)
{
    if (confirm(js_message))
    {
        window.location.href=js_url;
    }
}