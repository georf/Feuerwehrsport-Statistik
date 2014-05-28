editNews = (news) ->
  FssWindow.build('News bearbeiten')
  .add(new FssFormRowText('title', 'Titel', news.title))
  .add(new FssFormRowDate('date', 'Datum', news.date))
  .add(new FssFormRowHtml('content', 'Inhalt', news.content))
  .on('submit', (data) ->
    if news.id?
      data.id = news.id
      Fss.postReload('set-news', data)
    else 
      Fss.postReload('add-news', data)
  )
  .open()

$ ->
  $('.add-news').click () ->
    editNews
      title: null
      date: null
      content: null

  $('.edit-news').click () ->
    id = $(@).data('id')
    Fss.checkLogin () ->
      Fss.post 'get-news', newsId: id, (data) ->
        editNews(data.news)