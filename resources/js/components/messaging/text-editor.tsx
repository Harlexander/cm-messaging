import { CKEditor } from '@ckeditor/ckeditor5-react';
import {
  ClassicEditor,
  Bold,
  Essentials,
  Heading,
  Indent,
  IndentBlock,
  Italic,
  Link,
  List,
  MediaEmbed,
  Paragraph,
  Table,
  Undo
} from 'ckeditor5';
import { Editor } from '@tinymce/tinymce-react';

import 'ckeditor5/ckeditor5.css';
import { useRef } from 'react';

export default function TextEditor({ setMessage }: { setMessage: (message: string) => void }) {
  return (
   <div className='border border-gray-300 rounded-md p-2 w-full text-black'>
     <CKEditor
      onChange={(event, editor) => {
        const data = editor.getData();
        setMessage(data);
      }}
      editor={ ClassicEditor }
      
      config={ {
        licenseKey: 'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3NzI3NTUxOTksImp0aSI6IjZhMDdkN2UzLWE0OGUtNDc5ZC04M2U1LWQ0YzgxZTVlMWNkNyIsImxpY2Vuc2VkSG9zdHMiOlsiMTI3LjAuMC4xIiwibG9jYWxob3N0IiwiMTkyLjE2OC4qLioiLCIxMC4qLiouKiIsIjE3Mi4qLiouKiIsIioudGVzdCIsIioubG9jYWxob3N0IiwiKi5sb2NhbCJdLCJ1c2FnZUVuZHBvaW50IjoiaHR0cHM6Ly9wcm94eS1ldmVudC5ja2VkaXRvci5jb20iLCJkaXN0cmlidXRpb25DaGFubmVsIjpbImNsb3VkIiwiZHJ1cGFsIl0sImxpY2Vuc2VUeXBlIjoiZGV2ZWxvcG1lbnQiLCJmZWF0dXJlcyI6WyJEUlVQIl0sInZjIjoiMmYzODY1ZGUifQ.ysna2jjPd-jBXzz_fPvj4uA6ax3nHsDkdwwmBWo630WnvYR8tuIbjep89snOraYz8pEJJErT7TjIGUUuY9tCfw',
        toolbar: [
          // 'undo', 'redo', '|',
          'heading', '|', 'bold', 'italic', '|',
          'link', 'mediaEmbed', '|',
          'bulletedList', 'numberedList', 'indent', 'outdent'
        ],
        plugins: [
          Bold,
          Essentials,
          Heading,
          Indent,
          IndentBlock,
          Italic,
          Link,
          List,
          MediaEmbed,
          Paragraph,
          Table,
          Undo
        ],
        initialData: '<h1>Hello from CKEditor 5!</h1>',
      } }
    />
   </div>
  );
}

export function TextEditor2({ setMessage }: { setMessage: (message: string) => void }) {  
  return (
    <Editor
      apiKey='2hd427bmouiv0fr6vqp0mv0h3eknee13ln0oq9444gyoljp6'
      onChange={(_evt, editor) => setMessage(editor.getContent())}
      initialValue="<p>This is the initial content of the editor.</p>"
      init={{
        height: 500,
        menubar: false,
        plugins: [
          'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
          'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
          'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
          'bold italic forecolor | alignleft aligncenter ' +
          'alignright alignjustify | bullist numlist outdent indent | ' +
          'removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
      }}
    />
  )
}
